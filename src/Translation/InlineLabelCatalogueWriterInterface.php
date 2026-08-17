<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

use CoolMS\Core\Identity\UserInterface;

/**
 * Writes a `#[Translatable]` definition's inline per-locale labels into
 * the platform translation catalogue (a VFS XLIFF override).
 *
 * The bridge that closes the loop opened by {@see LabelResolverInterface}.
 * `LabelResolver` READS a definition's label for the current locale,
 * falling back to the source value when no override exists. This writer
 * is the WRITE side: an author who supplies `labels: { uk: "Колір", de:
 * "Farbe" }` alongside the source `label: "Color"` gets those
 * translations persisted as catalogue overrides, so `LabelResolver`
 * subsequently serves them.
 *
 * **Round-trip contract.** The writer addresses the same `(domain, key)`
 * coordinates `LabelResolver` reads from -- it asks the resolver for
 * `keyFor()` + `domainFor()` rather than reconstructing the convention.
 * What this writes, the resolver reads back. No skew possible.
 *
 * **Upsert semantics.** Writing labels for one definition must not
 * disturb sibling keys in the same `{domain}.{locale}.xlf` catalogue:
 * the implementation reads the existing catalogue, overlays only the
 * keys it owns, and persists the merged set. Re-writing the same labels
 * is idempotent.
 *
 * **Source value is NOT written here.** The source-locale text stays
 * inline on the definition (it's `LabelResolver`'s fallback). Only the
 * translated locales the author supplied land in the catalogue. Whether
 * to also write the default-locale value as an explicit override is the
 * caller's choice -- pass it in `$localizedLabels` if you want it.
 *
 * **Layering.** The contract lives in Core next to
 * {@see LabelResolverInterface} so consumer modules (Field, Navi,
 * Section, future Catalog) depend on the platform translation contract,
 * not on I18n's XLIFF/VFS machinery. The implementation lives in the
 * I18n module, where the catalogue read/write services are.
 */
interface InlineLabelCatalogueWriterInterface
{
    /**
     * Persist `$translatable`'s per-locale label overrides.
     *
     * @param array<string, array<string, ?string>> $localizedLabels
     *                                                               Map of `field => (locale => translated value)`. Each field
     *                                                               MUST be listed in the definition's `#[Translatable]` fields
     *                                                               (validated via {@see LabelResolverInterface::keyFor()};
     *                                                               misconfiguration throws before any catalogue write occurs,
     *                                                               so a bad field never leaves a partial write behind).
     *                                                               An empty map is a no-op. A `null` value REMOVES that locale's
     *                                                               override (clear-to-source: the resolver falls back to the
     *                                                               inline source value); an empty-string value is written
     *                                                               verbatim (a deliberate blank translation) — the two are
     *                                                               distinct.
     *
     * @throws TranslatableMisconfigurationException when `$translatable`
     *                                               is not `#[Translatable]`,
     *                                               a field is not listed,
     *                                               or no identifier can
     *                                               be derived
     */
    public function write(object $translatable, array $localizedLabels, UserInterface $actor): void;

    /**
     * Persist per-locale label overrides for a parent's translatable
     * INLINE children (e.g. a FieldDefinition's select options). The
     * write-side companion to {@see LabelResolverInterface::resolveChild()}:
     * what this writes, `resolveChild()` reads back, addressing the same
     * `(domain, childKey)` coordinates via {@see LabelResolverInterface::keyForChild()}.
     *
     * @param string                                               $childKind   The child kind declared in the parent's
     *                                                                          `#[Translatable(children: ...)]` (e.g. `'option'`).
     * @param array<string, array<string, array<string, ?string>>> $childLabels
     *                                                                          Map of `childId => (field => (locale => translated value))`.
     *                                                                          `childId` is the child's stable LOCAL id (an option `value`).
     *                                                                          Each `(childKind, field)` MUST be declared on the parent --
     *                                                                          validated up front so a bad coordinate throws before any
     *                                                                          catalogue write. Empty map / empty inner maps are no-ops. A
     *                                                                          `null` value REMOVES that locale's override (clear-to-source:
     *                                                                          the resolver falls back to the inline source value); an
     *                                                                          empty-string value is written verbatim (a deliberate blank
     *                                                                          translation) — the two are distinct. Upsert +
     *                                                                          sibling-preservation semantics are identical to {@see write()}.
     *
     * @throws TranslatableMisconfigurationException see
     *                                               {@see LabelResolverInterface::keyForChild()}
     */
    public function writeChildren(object $parent, string $childKind, array $childLabels, UserInterface $actor): void;
}
