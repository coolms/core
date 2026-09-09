<?php

declare(strict_types=1);
namespace CoolMS\Core\Field;

/**
 * Implement this interface (and tag the service `coolms.field.form_type`) to expose
 * additional Symfony form types in the Schema Editor's "Form Type" dropdown.
 *
 * The built-in `BuiltinFormTypeProvider` in the consuming application registers
 * the standard Symfony core types. Third-party bundles register custom types here.
 */
interface FormTypeProviderInterface
{
    /**
     * @return array<array{value: string, label: string}>
     *                                                    'value' is the Symfony FormType FQCN (e.g. TextType::class).
     *                                                    'label' is a human-readable name shown in the UI (e.g. 'Text').
     */
    public function getFormTypeOptions(): array;
}
