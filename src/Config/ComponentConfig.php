<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

/**
 * Immutable VO for a UI component config entry (type: component in YAML).
 *
 * Stores a flat map of named option groups -- CSS class sets, ARIA attributes, etc.
 * Has no concept of form fields, constraints, or data_class (those are form concerns).
 *
 * Examples:
 *   nav_menu: { attr: { class: navbar-nav }, item_attr: { class: nav-item }, ... }
 *   alert:    { attr: { class: alert alert-info, role: alert }, dismissible_attr: ... }
 */
final class ComponentConfig
{
    public string $id { get => $this->id; }

    /**
     * Flat map of named option groups.
     *
     * Examples:
     *   attr:             { class: navbar-nav }
     *   item_attr:        { class: nav-item }
     *   active_attr:      { class: nav-item active }
     *   dismissible_attr: { class: alert alert-info alert-dismissible fade show }
     *
     * @var array<string, mixed>
     */
    public array $options { get => $this->options; }

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->options = $data['options'] ?? $data['default_options'] ?? [];
    }

    /**
     * Return a named option group, e.g., get('attr'), get('item_attr').
     * Returns [] when the group is not defined -- safe to iterate in templates.
     *
     * @return array<string, mixed>
     */
    public function get(string $group): array
    {
        return $this->options[$group] ?? [];
    }
}
