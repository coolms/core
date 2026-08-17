<?php

declare(strict_types=1);

namespace CoolMS\Core\Attribute;

use Attribute;

/**
 * Annotates an entity class with a human-readable label and an
 * optional Phase 2 entity alias.
 *
 * `label` is consumed by `DoctrineEntitySchemaProvider` to enrich
 * `EntityClassMetadata` shown in Domain Explorer.
 *
 * `alias`, when present, registers the class with the
 * `EntityAliasRegistry` so DTMPL templates can declare entity
 * references via the `@alias` prefix in variable paths
 * (e.g., `{var:@identity_user.email}`). The `DtmplSyntaxValidator`
 * resolves the alias at schema-build time and stamps `entityType`
 * onto the resulting `ContextSchemaVariable`, removing the need to
 * run `coolms:document:set-entity-ref` for every template upload.
 *
 * `aliasCollection`, when present, registers a second alias for
 * the same class intended for collection-context lookups (e.g.,
 * `invoices` paired with singular `invoice`). The registry maps
 * both forms to the same class; widgets decide single vs collection
 * mode by their own key, independent of which alias form is used.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ClassMeta
{
    public function __construct(
        public string $label = '',
        public ?string $alias = null,
        public ?string $aliasCollection = null,
    ) {
    }
}
