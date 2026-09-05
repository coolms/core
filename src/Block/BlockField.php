<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * One field of a landing-page {@see BlockType}.
 *
 * Kinds:
 *  - `text`     — a single author string.
 *  - `textarea` — a single multi-line author string (rendered with line breaks).
 *  - `url`      — a single string the reader validates to a safe scheme
 *                 (relative / fragment / http(s) / mailto / tel), dropping
 *                 unsafe ones (e.g. `javascript:`).
 *  - `group`    — a repeated list of sub-objects (e.g. `features.items`,
 *                 `pricing.items`), each carrying its own {@see $itemFields}.
 *
 * Group sub-fields are themselves {@see BlockField}s — so a sub-field can be a
 * `url` and get the same scheme-validation a top-level `url` field does (e.g. a
 * gallery image `src`, a pricing plan `ctaUrl`). Sub-fields are never groups (no
 * nesting).
 *
 * Two consumers read this shape: the application's read-time normalizer, which
 * whitelists stored author data against it, and the catalog endpoint that
 * serializes it as the block editor's palette. Both live in the application
 * that installs this package, so they are named by ROLE here -- a package that
 * documents itself in terms of its consumer points at classes an installer
 * cannot resolve, in an IDE or anywhere else.
 */
final readonly class BlockField
{
    public const string KIND_TEXT = 'text';

    public const string KIND_TEXTAREA = 'textarea';

    public const string KIND_URL = 'url';

    public const string KIND_EMBED = 'embed';

    /**
     * One value from a FINITE, DECLARED list -- see {@see $options}.
     *
     * ⚠️ The kind exists so a block type can carry a small vocabulary without
     * each one inventing its own enum and its own validation. `cta.variant` is
     * the first: three sections of a real landing page mapped onto one `cta`
     * block and lost their distinct treatments, because the block could not say
     * which of the three it was.
     *
     * ⚠️ FINITE AND DECLARED, for the same reason block widths are. A theme
     * spends these on class names, and a class assembled at runtime from an
     * unconstrained stored string is one a content-scanning CSS build cannot
     * see and silently strips -- and one an author can typo into a selector
     * that matches nothing. A closed list means the stylesheet can name every
     * value it will ever be asked to render.
     */
    public const string KIND_CHOICE = 'choice';

    public const string KIND_GROUP = 'group';

    /**
     * @param 'text'|'textarea'|'url'|'embed'|'choice'|'group' $kind       the field's widget/validation kind. `embed`
     *                                                                     is a single video URL the reader resolves to
     *                                                                     a safe allow-listed embed src (YouTube /
     *                                                                     Vimeo), dropping anything else; `choice` is
     *                                                                     one value from `$options`
     * @param list<BlockField>                                 $itemFields sub-fields when `kind = group`; empty
     *                                                                     otherwise
     * @param string|null                                      $editor     an OPTIONAL editing-control hint for the
     *                                                                     admin
     *
     * ⚠️ `$editor` is a HINT, never a requirement. `kind` still governs
     * validation and storage; this only says "if you have a nicer control for
     * this, use it". An admin that does not recognise the key falls back to the
     * control `kind` implies, so a block type contributed by a module renders
     * and saves correctly in an older admin rather than breaking.
     *
     * It exists so a contributed block type does not force the admin to
     * hardcode it. Without the hint the only way to give `process_diagram` a
     * capture control is `if (block.type === 'process_diagram')` in the editor
     * -- an extensible registry beside a hardcoded consumer, which is the exact
     * defect the block-type contract was moved to Core to remove.
     */
    public function __construct(
        public string $name,
        public string $kind = self::KIND_TEXT,
        public array $itemFields = [],
        public ?string $editor = null,
        /**
         * The allowed values when `kind = choice`; empty otherwise.
         *
         * ⚠️ THE FIRST ENTRY IS THE DEFAULT, and the order is the palette's
         * order. Stating it positionally rather than as a separate `$default`
         * keeps the two from disagreeing -- a default that is not in the list
         * is a state nothing can render, and it is exactly the kind of thing
         * that survives review.
         *
         * @var list<string>
         */
        public array $options = [],
    ) {
    }

    public function isGroup(): bool
    {
        return self::KIND_GROUP === $this->kind;
    }

    public function isChoice(): bool
    {
        return self::KIND_CHOICE === $this->kind && [] !== $this->options;
    }

    /**
     * The value this field falls back to -- the first declared option.
     *
     * ⚠️ A choice field is never absent from a cleaned block. A template that
     * writes `block-cta--{var:block.variant}` would otherwise emit
     * `block-cta--` for content saved before the field existed, which is a
     * class that matches nothing and a section with no treatment at all.
     */
    public function defaultChoice(): string
    {
        return $this->options[0] ?? '';
    }

    public function isUrl(): bool
    {
        return self::KIND_URL === $this->kind;
    }

    public function isEmbed(): bool
    {
        return self::KIND_EMBED === $this->kind;
    }

    /**
     * @return array{name: string, kind: string, editor: string|null, options: list<string>, itemFields: list<array{name: string, kind: string, editor: string|null, options: list<string>, itemFields: list<mixed>}>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'kind' => $this->kind,
            // ⚠️ Emitted even when null. The palette endpoint is the admin's
            // ONLY view of a block type, so a hint that stays in PHP is a hint
            // no editor can act on -- and an absent key reads as "older backend"
            // rather than "no hint", which are different things.
            'editor' => $this->editor,
            // ⚠️ Emitted for every field, like `editor`, and for the same
            // reason: the palette endpoint is the admin's only view of a block
            // type, so a choice whose options stay in PHP is a select the
            // editor has to hardcode -- which is the extensible-registry-
            // beside-a-hardcoded-consumer defect this contract exists to
            // remove.
            'options' => $this->options,
            'itemFields' => array_map(static fn (self $f): array => $f->toArray(), $this->itemFields),
        ];
    }
}
