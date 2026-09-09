<?php

declare(strict_types=1);

namespace CoolMS\Core\Field;

use function array_key_exists;
use function array_keys;

/**
 * Canonical list of field names that cannot be used as dynamic field names in any entity.
 *
 * This is the design-time counterpart to `ExtrasProviderTrait::assertNotReserved()`
 * in `coolms/entity`, which enforces the same rule at runtime.
 *
 * !! It lives HERE because of what it names. Three of the four traits it
 * reserves for -- IdentifierProviderTrait, TimestampableTrait, BlameableTrait --
 * are in this package; the fourth, ExtrasProviderTrait, is in `coolms/entity`,
 * which requires this one. Core is therefore the only home that does not invert
 * a dependency. It previously sat in the platform's `Scaffolding` module, which
 * never referenced it: only the field-definition code did, across a module
 * boundary that phpstan had recorded as a violation.
 *
 * Grouped by source, because the reason is what makes an error message useful.
 */
final class ReservedFieldNames
{
    private const array RESERVED = [
        // ExtrasProviderTrait -- the bag itself; setting $extras['extras'] would be circular
        'extras' => 'reserved by ExtrasProviderTrait (the dynamic field bag itself)',
        // IdentifierProviderTrait -- identity is immutable and framework-managed
        'id' => 'reserved by IdentifierProviderTrait',
        // TimestampableTrait -- lifecycle timestamps are set by the persistence layer
        'createdAt' => 'reserved by TimestampableTrait',
        'updatedAt' => 'reserved by TimestampableTrait',
        'accessedAt' => 'reserved by TimestampableTrait',
        'expiresAt' => 'reserved by TimestampableTrait',
        // BlameableTrait -- set by the security layer, not user input
        'createdBy' => 'reserved by BlameableTrait',
        'updatedBy' => 'reserved by BlameableTrait',
        // SQL and ORM reserved words -- would corrupt the discriminator column or DDL
        'type' => 'reserved SQL and ORM keyword (discriminator column)',
        'table' => 'reserved SQL keyword in all major RDBMS',
        // NOTE: 'name' and 'description' are intentionally NOT reserved here.
        // They are native properties on entities that use NameProviderTrait /
        // DescriptionProviderTrait, but:
        //   a) Not every dynamic entity uses those traits; entities without them may
        //      legitimately store 'name' / 'description' in $extras.
        //   b) Entities that DO have those traits can still define a field definition
        //      for 'name' / 'description' to configure form presentation of the native
        //      property.  A dynamic form factory routes such fields to the declared
        //      class property (direct property_path) rather than to $extras.
        //   c) ExtrasProviderTrait::assertNotReserved() only guards 'extras' at runtime;
        //      native properties protect themselves via PHP property declarations.
    ];

    public static function isReserved(string $fieldName): bool
    {
        return array_key_exists($fieldName, self::RESERVED);
    }

    public static function getReasonFor(string $fieldName): ?string
    {
        return self::RESERVED[$fieldName] ?? null;
    }

    /** @return string[] */
    public static function all(): array
    {
        return array_keys(self::RESERVED);
    }
}
