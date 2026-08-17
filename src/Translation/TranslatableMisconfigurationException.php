<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

use InvalidArgumentException;

/**
 * Thrown when {@see LabelResolverInterface} is called on something it
 * cannot translate.
 *
 * **Not for missing translations.** Missing per-locale values fall back
 * to the source-locale value silently; that's intentional UX. This
 * exception fires only on PROGRAMMER errors:
 *
 *  - The target class carries no `#[Translatable]` attribute.
 *  - The requested `$field` is not in the attribute's `fields` list.
 *  - The field doesn't exist on the class at all.
 *  - The target has no resolvable identifier (no `getId()` / `id` / `slug`).
 *
 * Extends {@see InvalidArgumentException} because it represents a
 * caller contract violation. Each cause is reflected in the message so
 * consumers can act without reading exception subclasses.
 */
final class TranslatableMisconfigurationException extends InvalidArgumentException
{
    public static function notTranslatable(string $class): self
    {
        return new self(sprintf(
            'Class "%s" is not translatable: no #[Translatable] attribute. '
            . 'Add #[Translatable(fields: [\'label\', ...])] to the class to opt in.',
            $class,
        ));
    }

    /**
     * @param list<string> $listed
     */
    public static function fieldNotListed(string $class, string $field, array $listed): self
    {
        return new self(sprintf(
            'Field "%s" is not in the #[Translatable] fields for "%s". Listed: %s.',
            $field,
            $class,
            [] === $listed ? '(none)' : implode(', ', $listed),
        ));
    }

    /**
     * The `(childKind, field)` pair is not declared in
     * `#[Translatable(children: ...)]` -- the inline-child translation
     * seam ({@see LabelResolverInterface::keyForChild()}) was asked to key
     * a child kind/field the class never opted into.
     *
     * @param array<string, list<string>> $declared childKind => fields
     */
    public static function childFieldNotListed(string $class, string $childKind, string $field, array $declared): self
    {
        $rendered = [];
        foreach ($declared as $kind => $fields) {
            $rendered[] = sprintf('%s: [%s]', $kind, implode(', ', $fields));
        }

        return new self(sprintf(
            'Child field "%s.%s" is not declared in #[Translatable(children: ...)] for "%s". Declared: %s.',
            $childKind,
            $field,
            $class,
            [] === $rendered ? '(none)' : implode('; ', $rendered),
        ));
    }

    public static function fieldDoesNotExist(string $class, string $field): self
    {
        return new self(sprintf(
            'Field "%s" does not exist on "%s". Check #[Translatable] field list for typos.',
            $field,
            $class,
        ));
    }

    public static function noIdentifier(string $class): self
    {
        return new self(sprintf(
            'Cannot derive a catalogue key for "%s": no id / getId() / slug accessor found. '
            . 'Translatable definitions must expose a stable identifier so labels can be keyed.',
            $class,
        ));
    }
}
