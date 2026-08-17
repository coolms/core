<?php

declare(strict_types=1);

namespace CoolMS\Core\Attribute;

use Attribute;

/**
 * Declares field metadata for a static entity property.
 *
 * Used by FieldMetaReader to build field configuration without
 * ORM or framework dependency. Applied at the property level.
 *
 * All parameters are optional -- FieldMetaReader provides fallbacks
 * for any omitted value (e.g., label falls back to ucfirst of the
 * property name).
 *
 * Phase X-2.5 — filter/sort/search/enum properties added for the
 * `EntityFieldDescriptor` service that powers the document-generation
 * wizard's filter UI. Defaults preserve backwards compatibility:
 * existing usages without the new params behave exactly as before
 * (every new capability is opt-in, off by default).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FieldMeta
{
    /**
     * @param string|null          $label                 Human-readable label. Falls back to ucfirst(camelToWords($propertyName)).
     * @param string|null          $formType              Symfony Form type short name (e.g. 'text', 'textarea', 'number').
     * @param array<string, mixed> $formOptions           Symfony Form options passed to the form type
     * @param bool                 $showInForm            Whether this field appears in generated forms
     * @param array<string, mixed> $constraints           Validation rules map (e.g. ['NotBlank' => null, 'Length' => ['max' => 255]]).
     * @param string[]             $normalizationGroups   Serializer groups for reading (normalizing) this field
     * @param string[]             $denormalizationGroups Serializer groups for writing (denormalizing) this field
     * @param string[]             $securityRead          Roles allowed to read this field. Empty means all roles.
     * @param string[]             $securityWrite         Roles allowed to write this field. Empty means all roles.
     * @param int|null             $sortOrder             Display order in forms and grids
     * @param bool                 $private               Internal implementation detail — never expose in any UI.
     *                                                    Distinct from showInForm (form-only) and hidden (hidden input).
     * @param bool                 $filterable            Field may be used in RQL filter clauses (X-2.5).
     * @param list<string>|null    $filterOperators       Whitelist of CoolMS\Rql operator codes (eq, ne, gt, lt,
     *                                                    ge, le, cn, bw, ew, in, ni, null, nn). When null and
     *                                                    $filterable is true, EntityFieldDescriptor derives a
     *                                                    sensible default from the PHP property type.
     * @param bool                 $sortable              Field may be used in RQL sort clauses (X-2.5).
     * @param bool                 $searchable            Field participates in free-text search (X-2.5).
     * @param class-string|null    $enumClass             BackedEnum class describing legal values. When set, the
     *                                                    descriptor emits an `enumValues` map of case → label
     *                                                    so the frontend can render a select widget (X-2.5).
     */
    public function __construct(
        public ?string $label = null,
        public ?string $formType = null,
        public array $formOptions = [],
        public bool $showInForm = true,
        public array $constraints = [],
        public array $normalizationGroups = [],
        public array $denormalizationGroups = [],
        public array $securityRead = [],
        public array $securityWrite = [],
        public ?int $sortOrder = null,
        public bool $private = false,
        public bool $filterable = false,
        public ?array $filterOperators = null,
        public bool $sortable = false,
        public bool $searchable = false,
        public ?string $enumClass = null,
    ) {
    }
}
