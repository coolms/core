<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

use Attribute;

/**
 * Marks a class as carrying fields whose VALUES are display labels that
 * should be localised through the platform's translator runtime.
 *
 * **Definition-level, NOT instance-level.** This attribute belongs on
 * SCHEMA / DEFINITION classes (FieldDefinition, OptionDefinition,
 * NaviNode label, SiteSection label, etc.) -- the things whose values
 * are the same across every entity row that uses them. For per-row
 * localised content (Page / Article / Blog), the platform already uses
 * **variant Nodes** (one VFS child per locale), not translatable
 * fields. Variants and translatable definitions never mix.
 *
 * The author writes the source-locale value in the field as usual
 * (e.g. `$fieldDef->label = "Color"`); the runtime reads through
 * {@see LabelResolverInterface} which calls the translator with a key
 * derived from class + field. Other locales come from the I18n
 * catalogues (bundled `.xlf` + VFS overrides, the same chain
 * `VfsOverlayingTranslator` already serves). Operators edit per-locale
 * labels through the existing Translations admin or, in the future,
 * inline in the entity's own editor (Dynamic Entity / Catalog UX).
 *
 * **ORM-agnostic by construction.** No ORM listener, no entity
 * lifecycle hook, no metadata driver. The attribute is read by the
 * Application-layer resolver via PHP reflection; persistence stays
 * untouched by the translation seam. Mirrors the
 * `#[ServiceTaskHandler]` shape: pure metadata, behaviour wired in
 * Application + Infrastructure.
 *
 * **Choosing the domain.** When omitted, the resolver derives it from
 * the class's leading namespace segment (`Form`, `Navi`, `Section`, …)
 * lowercased. Operators rarely need to override;
 * the convention matches how the bundled `.xlf` files are organised
 * (`translations/form.en.xlf`, `translations/section.en.xlf`, etc.).
 * Explicit `domain` is for cross-module reuse cases (e.g. a workflow
 * definition that ships labels in the `workflow` domain even though
 * its class lives elsewhere).
 *
 * @see LabelResolverInterface for the read seam
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Translatable
{
    /**
     * @param list<string>                $fields   Field names on the target class whose
     *                                              string values are display labels that
     *                                              should resolve through the translator.
     *                                              Default `['label']` covers the most
     *                                              common shape (NaviNode, SiteSection,
     *                                              OptionValue, …).
     * @param string|null                 $domain   Translation domain. `null` = derive
     *                                              from class namespace. Operators
     *                                              override this when the target's
     *                                              labels belong in a foreign module's
     *                                              catalogue.
     * @param array<string, list<string>> $children Declares translatable
     *                                              INLINE children: a map of
     *                                              `childKind => list of translatable
     *                                              field names` per child. e.g.
     *                                              `['option' => ['label']]` for a
     *                                              FieldDefinition whose select options
     *                                              each carry a localizable `label`.
     *                                              Inline children are NOT entities and
     *                                              have no identifier of their own; they
     *                                              are addressed by a caller-supplied
     *                                              stable LOCAL id (the option `value`) via
     *                                              {@see LabelResolverInterface::keyForChild()}.
     *                                              This keeps lightweight inline children
     *                                              (JSON arrays) translatable without
     *                                              promoting them to first-class entities.
     *                                              Empty (default) = no translatable
     *                                              inline children.
     */
    public function __construct(
        public array $fields = ['label'],
        public ?string $domain = null,
        public array $children = [],
    ) {
    }
}
