<?php

declare(strict_types=1);

namespace CoolMS\Core\Theme;

/**
 * What a surface must load to render content the way a site renders it — the
 * theme's own stylesheets and scripts, already merged across its inheritance
 * chain.
 *
 * An L0 value object so any module can ask for it without importing Theme's
 * Application layer. Carries URLs, never file contents: the browser fetches
 * them from the same origin the public site does, which is what makes the
 * authoring surface load byte-identical CSS rather than a copy.
 */
final readonly class ThemeAuthoringAssets
{
    /**
     * Entry shape mirrors `ThemeAssets` deliberately — `{url}` rather than a
     * bare string. The SSR context passes the same records to the theme's
     * `{loop:site.theme.css}`, so an entry that later grows `media` or
     * `integrity` reaches the authoring surface too instead of being flattened
     * away at this boundary.
     *
     * @param list<array{url: string}> $css parent-first, so a child theme's rules win
     * @param list<array{url: string}> $js
     */
    public function __construct(
        public ?string $themeSlug = null,
        public array $css = [],
        public array $js = [],
    ) {
    }

    /** No theme resolved — the surface authors unstyled rather than not at all. */
    public static function none(): self
    {
        return new self();
    }
}
