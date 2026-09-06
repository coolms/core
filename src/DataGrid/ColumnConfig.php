<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

use CoolMS\Core\Form\FieldSecurityPolicy;

final readonly class ColumnConfig
{
    /**
     * @param array<string, mixed> $options
     * @param string|string[]|null $filterOp
     *
     * `$type` is one of the FE-side `DataGridColumnType` tokens, passed
     * through verbatim by `DataGridConfigBuilder` in the consuming application:
     *   - `text` (default), `boolean`, `number`, `enum`
     *   - `date`     -- calendar date (YYYY-MM-DD); FE renders
     *                  `<app-date-range-picker>` in the filter row.
     *   - `datetime` -- ISO-8601 timestamp; FE renders cells + filter in the
     *                  user's TZ via `UserCalendarPreferencesService.tz()`
     *   - `time`     -- wall-clock HH:MM[:SS]; FE renders
     *                  `<app-time-range-picker>` in the filter row.
     *
     * Rich cell types (display-only; configured through `$options`):
     *   - `badge`    -- value rendered as a coloured pill. `options.badgeMap`
     *                  ({value: variant}) picks the colour
     *                  (success|danger|warning|info|muted); falls back to a
     *                  convention map. `options.badgeLabels` ({value: label})
     *                  or `options.enumOptions` relabels the text.
     *   - `link`     -- value rendered as an `<a>`. `options.hrefPrefix`
     *                  (e.g. `mailto:` / `tel:`) is prepended; `options.hrefField`
     *                  / `options.textField` source the URL / text from other row
     *                  fields; `options.external: true` opens in a new tab.
     *   - `avatar`   -- an initials (or `options.imageField` image) avatar beside
     *                  the value; `options.subtitleField` adds a muted second line.
     *   - `snippet`  -- multi-line clamped text; `options.lines` sets the clamp
     *                  (default 2).
     * Anything richer is a registered cell widget -- see {@see CellWidgetConfig}.
     */
    public function __construct(
        public string $field,
        public string $label,
        public string $type = 'text',
        public bool $sortable = false,
        public bool $filterable = false,
        public bool $required = false,
        public bool $hideable = true,
        public bool $defaultVisible = true,
        public bool $writable = false,
        public ?int $width = null,
        public array $options = [],
        public ?FieldSecurityPolicy $security = null,
        /** RQL operator(s) the backend accepts for this column: string or string[]. */
        public string|array|null $filterOp = null,
        /**
         * Optional DB path override for RQL filtering.
         *
         * Maps the logical column ID (e.g., 'identifier') to the actual DB
         * relation path (e.g., 'identifiers.value'). When null, the column
         * ID is used directly as the DB field name.
         */
        public ?string $filterField = null,
        /**
         * Optional custom filter-row input. When set, the FE filter row
         * resolves `filterWidget.kind` to a registered widget component and
         * renders it instead of the type-driven input; null keeps the
         * historical type-driven behaviour. See {@see FilterWidgetConfig}.
         */
        public ?FilterWidgetConfig $filterWidget = null,
        /**
         * Optional custom cell renderer. When set, the FE resolves
         * `cellWidget.kind` to a registered component and renders it for every
         * cell of this column instead of the type-driven renderer; null keeps
         * the built-in `type`-driven rendering. See {@see CellWidgetConfig}.
         */
        public ?CellWidgetConfig $cellWidget = null,
        /**
         * Cell text wrapping. `null` (default) leaves the decision to the FE,
         * which single-line-truncates every cell EXCEPT `avatar` / `snippet`
         * types and custom cell widgets (those manage their own layout).
         * `false` opts any other column out of truncation so its text wraps.
         *
         * Present because the FE has always honoured `truncate` on its column
         * definition, but this VO had no such property -- so a YAML column
         * declaring `truncate: false` had it silently DROPPED here and the
         * flag never reached the browser. Every existing
         * declaration happened to sit on a `snippet` or `avatar` column, which
         * the FE already treats as no-truncate, so nothing looked broken; the
         * key was decorative. It works now, which matters the first time
         * someone puts it on a `text` column.
         */
        public ?bool $truncate = null,
    ) {
    }
}
