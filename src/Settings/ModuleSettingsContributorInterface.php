<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * A module declares the settings blocks it owns.
 *
 * Contributed rather than configured centrally, so a module that is not
 * installed contributes nothing and its settings simply do not appear -- the
 * same shape every other per-module surface uses here (NaviGraph toolbars,
 * field widgets, backup contributors).
 *
 * The `coolms.settings.contributor` tag is applied by an `_instanceof` rule in
 * `config/services.yaml`, NOT by an attribute here: a Domain contract must not
 * import the container it happens to run in, and phpstan's boundary rule
 * enforces exactly that.
 *
 * ⚠️ Belongs to the KERNEL rather than to whichever module implements the
 * settings surface, and the reason is a layering one. An application that
 * enforces module boundaries typically bars a module from importing a SIBLING
 * module's domain types, and the settings implementation sits low -- so while
 * it owned this type, every module beside it could READ a setting, because an
 * interface is permitted across that line, but could not DECLARE one, because
 * a definition is a concrete type. That leaves an application whose upper
 * layers are configurable and whose foundation is not. The kernel is the one
 * dependency every module may take, so the contract lives here.
 */
interface ModuleSettingsContributorInterface
{
    /**
     * @return list<ModuleSettingsDefinition>
     */
    public function getSettings(): array;
}
