<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

/**
 * A module-owned hook that overrides a built {@see FieldItem}'s presentation
 * from deploy/runtime configuration the generic form builder must stay ignorant
 * of.
 *
 * A form field opts in by declaring `options.adapter: <key>` in its config; the
 * builder, after building the FieldItem, looks up the adapter whose {@see key()}
 * matches and applies {@see overrides()} via {@see FieldItem::withOverrides()}.
 * Because this happens at BUILD time (inside
 * `FormConfigRenderBuilder` in the consuming application), the override
 * flows uniformly to every surface -- SSR widget, the `/forms/{id}/render` API,
 * and the admin form-builder preview.
 *
 * The adapter returns a plain partial-override map (NOT a FieldItem) so an
 * implementing module never imports the Form Domain VO -- only this interface.
 *
 * Example: Identity's identifier field overrides its label + input type from the
 * `allowed_registration_methods` deploy config (Email vs "Email or username" vs
 * "Email, phone, or username"; type=email only when email is the sole method),
 * keeping that logic out of the Form module.
 *
 * Tagged `coolms.form.field_adapter`.
 */
interface FieldItemAdapterInterface
{
    /** The `options.adapter` value this adapter handles (e.g. 'identity.identifier'). */
    public function key(): string;

    /**
     * Partial overrides merged onto the field by the builder. Only the keys
     * present are applied; absent keys keep the field's built values.
     *
     * @return array{label?: string, type?: string, autocomplete?: string|null}
     */
    public function overrides(): array;
}
