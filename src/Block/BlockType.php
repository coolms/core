<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * A landing-page section block type (ADR-130, W5.b) — its stable `id` (the
 * `block.type` discriminator the theme dispatches on), a human `label`, and the
 * ordered {@see BlockField} schema that declares which fields the block carries.
 *
 * The {@see \App\Content\Application\Service\BlockTypeRegistry} owns the built-in
 * set; the read-time normalizer validates author data against it, and the
 * `/content/landing-blocks` catalog endpoint serializes it as the block-editor's
 * palette.
 */
final readonly class BlockType
{
    /**
     * @param list<BlockField> $fields
     */
    public function __construct(
        public string $id,
        public string $label,
        public array $fields,
    ) {
    }

    /**
     * @return array{id: string, label: string, fields: list<array{name: string, kind: string, itemFields: list<array{name: string, kind: string, itemFields: list<mixed>}>}>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'fields' => array_map(static fn (BlockField $f): array => $f->toArray(), $this->fields),
        ];
    }
}
