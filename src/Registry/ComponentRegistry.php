<?php

declare(strict_types=1);

namespace CoolMS\Core\Registry;

use CoolMS\Core\Config\ComponentConfig;
use InvalidArgumentException;

/**
 * Registry for UI component configs (type: component in YAML).
 *
 * Stores platform UI primitives: navigation configs, alert styles, badges,
 * pagination -- anything that is not a form or field type descriptor.
 *
 * Populated at kernel boot time by FileConfigScanner when it encounters
 * `type: component` entries in coolms.form.scan_directories.
 *
 * Widget renderers call getOptions() for graceful degradation: returns []
 * when no active theme registers the id -- templates render semantic HTML
 * with no classes attached.
 *
 * @see FormConfigRegistry -- forms and field type descriptors (type: form / form_type)
 */
final class ComponentRegistry
{
    /**
     * @var array<string, ComponentConfig>
     */
    private array $components = [];

    /**
     * @param ComponentConfig|array<string, mixed> $config
     */
    public function register(string $id, ComponentConfig|array $config): void
    {
        $this->components[$id] = $config instanceof ComponentConfig
            ? $config
            : new ComponentConfig($config);
    }

    /**
     * @throws InvalidArgumentException when id is unknown
     */
    public function get(string $id): ComponentConfig
    {
        return $this->components[$id]
            ?? throw new InvalidArgumentException("Component config '$id' not found.");
    }

    public function has(string $id): bool
    {
        return isset($this->components[$id]);
    }

    /**
     * Return options for a component id, or [] when not registered.
     *
     * Safe convenience method -- widget renderers use this for graceful degradation:
     * when no active theme registers the id the registry returns [] and templates
     * render with no classes (semantic HTML fallback).
     *
     * @return array<string, mixed>
     */
    public function getOptions(string $id): array
    {
        return $this->has($id) ? $this->components[$id]->options : [];
    }
}
