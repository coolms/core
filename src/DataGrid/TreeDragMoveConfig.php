<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

/**
 * Drag-and-drop sub-config for tree mode.
 *
 *  - `enabled`     -- when false, drag-and-drop is disabled even if the block is present.
 *  - `targetTypes` -- list of allowed `nodeType` values for drop targets, e.g.,
 *                    `['directory']`. Drops on rows whose `nodeType` is not in
 *                    this list are rejected by the FE drop-cursor logic.
 *
 * Constructed only when the YAML carries a `tree.dragMove:` block; absence
 * means drag-and-drop is off entirely.
 *
 * Validation: `enabled = true` is only legal when the parent `TreeConfig::enabled`
 * is also true; enforced by `DataGridConfigBuilder`.
 */
final readonly class TreeDragMoveConfig
{
    /** @param list<string> $targetTypes */
    public function __construct(
        public bool $enabled = false,
        public array $targetTypes = ['directory'],
    ) {
    }
}
