<?php

declare(strict_types=1);

namespace CoolMS\Core\Theme;

/**
 * Read port: "which stylesheets does the site use for this section?".
 *
 * Lives in Core L0 so Content — and anything else that grows an authoring or
 * preview surface — can ask without importing Theme's Application layer. The
 * contract sits BELOW both modules rather than one importing the other, the
 * same move that removed the Navi and Terminal cross-module suppressions.
 *
 * Implemented in Theme, where the resolver and the asset chain already live.
 * Never throws: a section with no resolvable theme is a legitimate state, and
 * the caller's job is to keep working unstyled rather than to fail.
 */
interface ThemeAuthoringAssetsPortInterface
{
    /**
     * @param string $sectionSlug empty string means "the default section",
     *                            matching how the render path resolves a theme
     */
    public function forSection(string $sectionSlug): ThemeAuthoringAssets;
}
