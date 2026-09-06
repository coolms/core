<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

/**
 * Declares a custom filter-row input for a DataGrid column.
 *
 * The filter-widget axis is the parallel of the field-widget registry
 * (``FieldWidgetRegistry``), but operator-aware: a
 * column can opt a column out of the type-driven filter inputs (text / boolean
 * / range / enum) and into a registered widget instead -- e.g. an option-source
 * select, a tag picker, or a relation picker -- rendered in the filter row.
 *
 * `$kind` is the stable token the front-end {@see DataGridFilterWidgetRegistry}
 * resolves to an Angular component; `$options` is an opaque per-widget config
 * bag merged on top of the column's `options` on the wire. The operators the
 * widget owns are NOT carried here -- they are derived from the column's
 * `filterOp`, so a range widget cleanly receives its `ge`/`le` pair and a
 * single-op widget receives its one operator.
 */
final readonly class FilterWidgetConfig
{
    /** @param array<string, mixed> $options */
    public function __construct(
        public string $kind,
        public array $options = [],
    ) {
    }
}
