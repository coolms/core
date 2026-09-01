<?php

declare(strict_types=1);

namespace CoolMS\Core\Theme;

/**
 * Contract for Composer packages that ship themes.
 *
 * Implementations must be tagged with 'coolms.theme_provider' in their
 * DI configuration so that the Theme Infrastructure layer can collect them.
 * Tagging is done automatically by ThemeBundle's Extension via
 * registerForAutoconfiguration(ThemeProviderInterface::class).
 */
interface ThemeProviderInterface
{
    /**
     * Unique theme slug (e.g., 'coolms-default').
     * Must match the slug declared in theme.yaml.
     */
    public string $slug { get; }

    /**
     * Absolute filesystem path to the theme's templates directory.
     *
     * This path is stored as ThemeSource::$fsPath and used by the DTMPL renderer
     * as the base directory for template lookups. It must be the directory that
     * directly contains the template files (e.g., layouts/, pages/, partials/).
     *
     * Convention: <bundle-root>/templates/
     */
    public string $themePath { get; }

    /**
     * Absolute path to theme.yaml.
     *
     * The manifest lives one level above the templates directory, at the bundle root.
     * Convention: <bundle-root>/theme.yaml
     */
    public string $manifestPath { get; }

    /**
     * Absolute path to compiled frontend assets.
     */
    public string $assetsPath { get; }

    /**
     * Web-accessible URL prefix for assets (e.g., '/themes/coolms-default').
     */
    public string $assetsUrl { get; }
}
