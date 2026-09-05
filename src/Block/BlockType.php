<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * A landing-page section block type: its stable `id` (the `block.type`
 * discriminator a theme dispatches on), a human `label`, and the ordered
 * {@see BlockField} schema declaring which fields the block carries.
 *
 * ⚠️ The `id` is what stored content REFERENCES, so renaming one orphans every
 * block already using it. Add a type rather than rename one.
 *
 * The application aggregates these from every registered provider, validates
 * stored author data against the schema, and serializes the set as the block
 * editor's palette. Named by role rather than by class: those live in the
 * application that installs this package, and a `{@see}` pointing out of the
 * archive resolves to nothing for whoever installed it.
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
