<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

final readonly class DataGridConfig
{
    /**
     * @param ColumnConfig[]                                                                                                                                                            $columns
     * @param array<array{id: string, label: string, icon?: string|null, confirm: bool, route?: string|null, routeParams?: array<string,string>, showWhen?: array<string, mixed>|null}> $rowActions
     *
     * `showWhen` (optional, per action) carries the same predicate
     * shape the toolbar navi tree already uses
     * (`{field, op, value}` with optional `and`/`or` nesting). The FE
     * evaluates it against the row's projection -- `delete` for the
     * user's default personal calendar, for instance, can be hidden
     * with `{field: isDefaultPersonal, op: eq, value: false}`. Mirror
     * of the toolbar's row-level gating.
     */
    public function __construct(
        public string $id,
        public string $title,
        public DataGridDataSource $dataSource,
        public array $columns,
        public array $rowActions = [],
        public bool $draggable = false,
        public ?string $reorderRoute = null,
        public string $loadingMode = 'eager',
        /**
         * When false, the inline action-button column is hidden (context-menu-only mode).
         * Touch devices restore the column via @media (hover: none) CSS in the frontend.
         */
        public bool $showActionColumn = true,
        /**
         * Tree-mode block. Absent (null) for flat grids; present-and-enabled
         * triggers the FE chevron + indent + lazy per-parent children pipeline.
         */
        public ?TreeConfig $tree = null,
        /**
         * Plural noun for the grid's EMPTY state -- "contacts", "backup
         * bundles" -- so an unfiltered empty grid reads "No contacts yet"
         * instead of a generic line. Null = keep the generic wording.
         *
         * Deliberately not reusing `$title`: every shipped grid YAML declares
         * `label: ''`, so there is no existing field carrying a usable noun.
         * A filtered-empty grid ignores this and says "No matches" instead --
         * the two situations need different words.
         */
        public ?string $emptyLabel = null,
    ) {
    }
}
