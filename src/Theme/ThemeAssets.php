<?php

declare(strict_types=1);

namespace CoolMS\Core\Theme;

/**
 * Resolved CSS and JS asset URLs for a theme.
 *
 * Produced by ThemeAssetsProviderInterface implementations and consumed
 * by TemplateContextBuilder to populate site.theme.css / site.theme.js.
 */
final readonly class ThemeAssets
{
    /**
     * @param list<array{url: string}> $css
     * @param list<array{url: string}> $js
     */
    public function __construct(
        public array $css = [],
        public array $js = [],
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }
}
