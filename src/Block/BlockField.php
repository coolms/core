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

    public const string KIND_GROUP = 'group';

    /**
     * @param 'text'|'textarea'|'url'|'embed'|'group' $kind       the field's widget/validation kind. `embed` is a
     *                                                            single video URL the reader resolves to a safe
     *                                                            allow-listed embed src (YouTube / Vimeo), dropping
     *                                                            anything else
     * @param list<BlockField>                        $itemFields sub-fields when `kind = group`; empty otherwise
     * @param string|null                             $editor     an OPTIONAL editing-control hint for the admin
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
    ) {
    }

    public function isGroup(): bool
    {
        return self::KIND_GROUP === $this->kind;
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
     * @return array{name: string, kind: string, editor: string|null, itemFields: list<array{name: string, kind: string, editor: string|null, itemFields: list<mixed>}>}
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
            'itemFields' => array_map(static fn (self $f): array => $f->toArray(), $this->itemFields),
        ];
    }
}
