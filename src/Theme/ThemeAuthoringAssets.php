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
     * Shares the `{url}` entry shape with {@see ThemeAssets}, and diverges from
     * it in exactly two ways -- which is why both types exist rather than one.
     * This one carries the resolved `$themeSlug`, and its lists are already
     * merged across the theme inheritance chain, parent-first. ThemeAssets is a
     * single theme's own assets, as that theme's provider returned them.
     *
     * The shared half is pinned by ThemeAssetsShapeTest, not by this comment: an
     * entry that later grows `media` or `integrity` has to grow in both, or the
     * SSR context and the authoring surface stop agreeing on what a stylesheet
     * record is. A docblock asking a human to keep two types in sync is the kind
     * of guarantee that goes unenforced.
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
