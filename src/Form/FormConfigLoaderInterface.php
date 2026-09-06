<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

/**
 * Runtime override loader for form field configurations.
 *
 * Implementations provide field-level overrides merged on top of file-based
 * configs when a form is built. Loaders with higher priority win.
 */
interface FormConfigLoaderInterface
{
    /**
     * Return field-level overrides indexed by the field name for the given form.
     *
     * @param string $formId the form ID (matches the `id` key in form config files)
     *
     * @return array<string, array<string, mixed>>
     */
    public function loadFieldOverrides(string $formId): array;

    /**
     * Loaders are applied in ascending priority order; the highest value wins.
     * Suggested values: file = 0, database = 100.
     */
    public function getPriority(): int;
}
