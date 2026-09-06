<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class DataSourceDefinition
{
    /**
     * @param DataSourceOption[]   $options       resolved options for static/enum types
     * @param array<string, mixed> $widgetOptions free-form widget-specific config (e.g.,
     *                                            media-picker bindTarget / display / etc.)
     */
    public function __construct(
        public DataSourceType $type,
        public string $bindValue = 'value',
        public string $bindLabel = 'label',
        public bool $multiple = false,
        public ?int $maxItems = null,
        public array $options = [], // populated for static + enum
        public ?string $url = null, // populated for api + repo
        /**
         * Widget variant for rendering.
         * select        — native <select> (default, ≤8 options or explicit)
         * select-search — custom dropdown with inline search (>8 options or explicit)
         * select-tree   — hierarchical dropdown built from parentId field in API response.
         * media-picker — visual media browser (see the media picker docs).
         */
        public string $widget = 'select',
        /**
         * Loading strategy.
         * eager — fetch all options once on form open, filter on client (default)
         * lazy  — fetch on dropdown open + debounced server-side search (future).
         */
        public string $loading = 'eager',
        /**
         * Free-form widget configuration. Populated from YAML key `widgetOptions:`
         * under `dataSource:`; passed through to the Angular widget verbatim. Kept
         * separate from `options` (which is the resolved static-option list) so
         * widget-specific knobs like media-picker `bindTarget`/`display`/`accept`
         * don't collide with static-option entries.
         */
        public array $widgetOptions = [],
    ) {
    }
}
