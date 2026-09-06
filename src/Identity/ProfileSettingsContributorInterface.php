<?php

declare(strict_types=1);
namespace CoolMS\Core\Identity;

interface ProfileSettingsContributorInterface
{
    /**
     * Unique section key, snake_case: 'ui', 'calendar', 'notifications'.
     */
    public function getSection(): string;

    /**
     * Human-readable label shown as tab title in the Profile page.
     */
    public function getLabel(): string;

    /**
     * Bootstrap icon name for the tab icon (without 'bi-' prefix).
     */
    public function getIcon(): string;

    /**
     * Form config ID to render via DynamicFormComponent.
     * Format: '{module}:user_settings', e.g. 'identity:ui_settings'.
     */
    public function getFormId(): string;

    /**
     * Default values for this section.
     * User-stored overrides are merged on top of these at read time.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array;
}
