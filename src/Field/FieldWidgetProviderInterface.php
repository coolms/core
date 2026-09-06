<?php

declare(strict_types=1);
namespace CoolMS\Core\Field;

/**
 * Implement this interface (auto-tagged `coolms.field.widget_provider`) to map
 * a field **type** to the richer admin input ("widget") a module provides for
 * it — so the admin editor renders that widget for any field declared with
 * that type, without the editor knowing the contributing module.
 *
 * Distinct from {@see FormTypeProviderInterface}, which lists Symfony FormType
 * FQCNs for the *schema editor's* "Form Type" dropdown (server-side form
 * building). This axis is about the **admin field-panel** front-end widget
 * (e.g. a tags input, a user picker): each provider declares one field type and
 * the front-end descriptor the field-panel API attaches to fields of that type.
 *
 * Module gating is automatic: a provider that lives in module X only exists in
 * the container when X is installed, so its widget simply isn't offered when X
 * is absent — a field of that type falls back to the built-in input.
 */
interface FieldWidgetProviderInterface
{
    /**
     * The field `type` (as declared in a `vfs_node` field YAML, e.g. `tags`)
     * this provider supplies a widget for.
     */
    public function fieldType(): string;

    /**
     * The front-end widget descriptor for that type. Must carry a `kind`
     * (the FE widget key the admin maps to a component); other keys are
     * widget-specific config (e.g. a `suggestionsUrl`, `multiple`).
     *
     * The field's resolved schema config is passed in so a provider can
     * specialise the descriptor per field — e.g. the Taxonomy widget reads a
     * `widget: { tree: <code> }` key to scope itself to a specific tree, instead
     * of every `taxonomy` field sharing one hardcoded tree. Providers that need
     * no field-level config simply ignore the argument.
     *
     * @param array<string, mixed> $fieldConfig the resolved schema config for the
     *                                          field this widget is rendered for
     *
     * @return array<string, mixed>
     */
    public function widget(array $fieldConfig = []): array;
}
