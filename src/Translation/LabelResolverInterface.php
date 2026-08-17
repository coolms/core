<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

/**
 * Reads localised display labels off a `#[Translatable]`-annotated
 * definition.
 *
 * **Two-step resolution per call:**
 *   1. Compute a catalogue key from (class, field, optional row id)
 *      via {@see keyFor()}.
 *   2. Call the platform translator (Symfony, decorated by
 *      `VfsOverlayingTranslator`) with `(key, [], domain, locale)`.
 *      Locale `null` means "use the current request locale" -- the
 *      runtime defers to the i18n module's locale resolver.
 *
 * **Fallback when no translation exists:** the literal source-locale
 * value on `$definition->{$field}` is returned verbatim. Authors write
 * the source text inline as a normal entity field; missing
 * translations don't show empty cells in the admin -- they show the
 * source-locale value with no fanfare. Same UX as Symfony's default
 * `translator.fallback_locales` chain.
 *
 * **Key derivation is exposed deliberately.** Other modules need to
 * round-trip: the deploy-time bridge that writes inline `labels: { uk:
 * "..." }` into a VFS XLIFF override has to know which key to write to.
 * Exposing {@see keyFor()} as part of the contract removes the
 * temptation to hard-code key conventions across modules; the
 * resolver is the single source of truth.
 *
 * **What this interface does NOT do:**
 *   - It does not read or write VFS XLIFF files directly. That's
 *     `VfsCatalogueLoaderInterface`'s job, already wired by F5.a/b.
 *   - It does not resolve per-row content (Page variant titles, Article
 *     body). Those are variant Nodes; LabelResolver only handles
 *     definition-level metadata.
 *   - It does not pluralise or format. Symfony's translator already
 *     does ICU MessageFormat; consumers that need plurals call the
 *     translator directly with the key from {@see keyFor()}.
 */
interface LabelResolverInterface
{
    /**
     * Resolve `$definition->{$field}` for `$locale` (current locale when
     * null). Returns the source-locale value as fallback when no
     * translation exists. Never throws on missing translations -- only
     * on missing `#[Translatable]` or non-existent `$field`.
     *
     * @throws TranslatableMisconfigurationException when `$definition`'s
     *                                               class carries no
     *                                               `#[Translatable]`,
     *                                               or `$field` is not
     *                                               in the configured
     *                                               list, or the field
     *                                               doesn't exist on
     *                                               the class
     */
    public function resolve(object $definition, string $field, ?string $locale = null): string;

    /**
     * Catalogue key the resolver writes / reads for the
     * `(class, field, definitionId)` triple.
     *
     * The shape is stable across builds: `{className}.{id}.{field}`
     * where `{className}` is the snake_case'd short class name and
     * `{id}` is the definition's identifier (whatever
     * `getId(): UuidInterface|string` returns, stringified). For
     * definitions identified by a non-UUID slug (e.g. NaviNode's
     * `slug`), the slug stands in for `{id}`.
     *
     * The key does NOT embed the translation domain. The domain is the
     * catalogue FILE (`{domain}.{locale}.xlf`, see {@see domainFor()});
     * this key is the trans-unit id INSIDE that file. A writer addressing
     * a catalogue needs both.
     *
     * @throws TranslatableMisconfigurationException when `$definition`'s
     *                                               class is not
     *                                               translatable, or
     *                                               `$field` is unknown,
     *                                               or no identifier
     *                                               can be derived
     */
    public function keyFor(object $definition, string $field): string;

    /**
     * Catalogue key for a translatable INLINE child's field.
     *
     * Inline children (e.g. a FieldDefinition's select options) are not
     * entities and carry no identifier of their own; the caller supplies
     * a stable LOCAL id (the option `value`). The key shape is
     * `{className}.{parentId}.{childKind}.{childId}.{field}` -- the
     * parent's own identifier (from {@see keyFor()}'s derivation) plus the
     * child coordinates. Like {@see keyFor()}, the key does NOT embed the
     * domain ({@see domainFor()} owns the catalogue file).
     *
     * Translation keys are opaque strings to the catalogue (Symfony keys
     * routinely contain dots), so the option value is used verbatim --
     * uniqueness is guaranteed by its position in the key, no encoding is
     * required.
     *
     * @throws TranslatableMisconfigurationException when `$parent`'s class
     *                                               carries no `#[Translatable]`, the `(childKind, field)` pair is
     *                                               not declared in `#[Translatable(children: ...)]`, or no parent
     *                                               identifier can be derived.
     */
    public function keyForChild(object $parent, string $childKind, string $childId, string $field): string;

    /**
     * Resolve a translatable inline child's `$field` for `$locale`
     * (current locale when null). Returns `$sourceValue` as the fallback
     * when no catalogue override exists.
     *
     * Because an inline child is not reflectable off the parent (it lives
     * in a JSON array, not a typed property), the caller passes the
     * source-locale `$sourceValue` explicitly -- the analogue of how
     * {@see resolve()} reads the source value off the entity field.
     *
     * @throws TranslatableMisconfigurationException see {@see keyForChild()}
     */
    public function resolveChild(
        object $parent,
        string $childKind,
        string $childId,
        string $field,
        string $sourceValue,
        ?string $locale = null,
    ): string;

    /**
     * The translation domain (catalogue file stem) for `$definition`'s
     * class: either the explicit `#[Translatable(domain: ...)]` value or
     * the namespace-derived default (a `Field` module class -> `field`).
     *
     * Companion to {@see keyFor()}. The key alone is not enough to write
     * a per-locale override -- the inline-label bridge must address the
     * right `{domain}.{locale}.xlf` catalogue, and the domain derivation
     * has a single owner here so no module re-implements the convention.
     *
     * Note the key returned by {@see keyFor()} does NOT embed the domain;
     * the domain is the catalogue file, the key is the trans-unit id
     * inside it. Callers writing a catalogue need both.
     *
     * @throws TranslatableMisconfigurationException when `$definition`'s
     *                                               class carries no
     *                                               `#[Translatable]`
     */
    public function domainFor(object $definition): string;
}
