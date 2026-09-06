<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

/**
 * Declares a custom cell renderer for a DataGrid column.
 *
 * The cell-widget axis is the display-side parallel of the field-widget registry
 * (``FieldWidgetRegistry``) and the filter-widget axis
 * ({@see FilterWidgetConfig}): a column can opt its cells out of the type-driven
 * renderers (text / badge / link / avatar / snippet / date…) and into a
 * registered Angular component instead — e.g. a sparkline, a progress bar, or a
 * composite multi-field cell.
 *
 * `$kind` is the stable token the front-end `DataGridCellWidgetRegistry`
 * resolves to a component; `$options` is an opaque per-widget config bag merged
 * on top of the column's `options` on the wire. The widget receives the cell
 * value, the full row, and that merged config — cells are display-only, so
 * (unlike a field widget) there is no change callback.
 *
 * Most rich cells need no widget at all: the built-in `type` tokens
 * (`badge`, `link`, `avatar`, `snippet`) cover the common cases declaratively
 * through `options`. The registry is the additive escape hatch for anything the
 * built-ins don't express.
 */
final readonly class CellWidgetConfig
{
    /** @param array<string, mixed> $options */
    public function __construct(
        public string $kind,
        public array $options = [],
    ) {
    }
}
