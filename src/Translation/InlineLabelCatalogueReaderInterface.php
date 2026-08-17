<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

/**
 * Reads the explicit per-locale label overrides for a `#[Translatable]`
 * parent's inline children (e.g. a FieldDefinition's select options).
 *
 * The read companion to {@see InlineLabelCatalogueWriterInterface::writeChildren()}.
 * Where {@see LabelResolverInterface::resolveChild()} returns the
 * *effective* value for one locale (override, else source fallback), this
 * returns only the locales that carry an **explicit override** — so an
 * editor can pre-fill exactly what's been authored and leave untranslated
 * locales blank, distinct from "shows the source as if it were a
 * translation".
 *
 * Addresses the same `(domain, key)` coordinates the writer wrote and the
 * resolver reads, via {@see LabelResolverInterface::keyForChild()} — no
 * skew. The caller supplies the child ids (an inline child has no
 * identifier of its own; the parent knows them, e.g. the option `value`s).
 *
 * Layering mirrors the writer: the contract lives in Core so consumer
 * modules depend on the platform translation seam, not I18n's XLIFF/VFS
 * machinery; the implementation lives in I18n where the catalogue
 * read service is.
 */
interface InlineLabelCatalogueReaderInterface
{
    /**
     * @param list<string> $childIds Stable local ids of the children to read
     *                               (e.g. select-option `value`s). Empty = no-op.
     *
     * @throws TranslatableMisconfigurationException see
     *                                               {@see LabelResolverInterface::keyForChild()}
     *
     * @return array<string, array<string, string>> Map of
     *                                              `childId => (locale => override value)`. Only children/locales
     *                                              with an explicit override appear; a child with no overrides is
     *                                              absent from the map entirely.
     */
    public function readChildLabels(object $parent, string $childKind, array $childIds, string $field): array;

    /**
     * Entity-level mirror of {@see readChildLabels()}: reads the explicit
     * per-locale overrides for a `#[Translatable]` parent's OWN fields (e.g.
     * a FieldDefinition's `label`), addressing the same `(domain, key)`
     * coordinates the writer wrote via {@see LabelResolverInterface::keyFor()}
     * + {@see LabelResolverInterface::domainFor()}. Where {@see readChildLabels()}
     * reads inline-child fields, this reads top-level entity fields.
     *
     * Same override-only contract: only locales an operator actually authored
     * appear (a `null` catalogue override is a baseline-only row, skipped), so
     * an editor pre-fills exactly what exists and leaves untranslated locales
     * blank rather than echoing the source value as if it were a translation.
     *
     * @param list<string> $fields Translatable entity field names to read
     *                             (e.g. `['label']`). Empty = no-op.
     *
     * @throws TranslatableMisconfigurationException see
     *                                               {@see LabelResolverInterface::keyFor()}
     *
     * @return array<string, array<string, string>> Map of
     *                                              `field => (locale => override value)`. Only fields/locales with
     *                                              an explicit override appear; a field with no overrides is absent
     *                                              from the map entirely.
     */
    public function readLabels(object $parent, array $fields): array;
}
