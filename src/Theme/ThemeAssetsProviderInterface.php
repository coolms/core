<?php

declare(strict_types=1);

namespace CoolMS\Core\Theme;

/**
 * Contract for theme asset resolution.
 *
 * Each theme package implements this interface and returns the CSS/JS URLs
 * for its compiled assets. The implementation decides how to resolve them  --
 * Vite manifest, static list, empty (SPA), etc.
 *
 * Implementations are auto-tagged via 'coolms.theme_assets_provider' and
 * collected by ThemeAssetsRegistry, keyed by theme slug.
 */
interface ThemeAssetsProviderInterface
{
    /**
     * Must match ThemeProviderInterface::$slug for the same theme package.
     */
    public string $slug { get; }

    /**
     * Resolve compiled asset URLs for this theme.
     *
     * @param string $assetsPath Absolute filesystem path to theme's public assets dir
     * @param string $assetsUrl  Web-accessible URL prefix (e.g., '/themes/coolms-default')
     */
    public function getAssets(string $assetsPath, string $assetsUrl): ThemeAssets;
}
