<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

/**
 * Tree-mode configuration block for a DataGrid.
 *
 * Opt-in: when a `tree:` block is present on a YAML datagrid config the FE
 * renders a chevron column + indent + lazy per-parent children fetches.
 * Default-off; existing flat grids stay flat.
 *
 *  - `parentColumn`        -- row key carrying the parent UUID; default `parentId`.
 *  - `hasChildrenStrategy` -- `optimistic` always renders a chevron on container
 *                             rows; `computed` requires the provider to set a
 *                             real `hasChildren: bool` on each row.
 *  - `rootFilter`          -- optional RQL expression bounding the root set
 *                             (provider-interpreted; the datagrid runtime does
 *                             not enforce it).
 *  - `dragMove`            -- when present + `enabled: true`, the FE wires HTML5
 *                             drag-and-drop on rows; drop on a row whose `nodeType`
 *                             matches one of `targetTypes` issues a VFS `mv`.
 */
final readonly class TreeConfig
{
    public function __construct(
        public bool $enabled = true,
        public string $parentColumn = 'parentId',
        public string $hasChildrenStrategy = 'optimistic',
        public ?string $rootFilter = null,
        public ?TreeDragMoveConfig $dragMove = null,
    ) {
    }
}
