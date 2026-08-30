<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * Whether a name is set in the process environment.
 *
 * A port, not a static call, because "is this setting pinned by the deployment"
 * is a decision the settings tier makes on every request and must be able to
 * test both ways.
 *
 * ⚠️ **Presence, never the value.** The value an operator pinned is already in
 * the container parameter the module ships with -- `%env(int:default:…:PAGE_CACHE_TTL)%`
 * resolves to the environment when the variable is set and to the fallback when
 * it is not. So the only thing left to ask is whether it WAS set, and reading
 * the value here would be a second, competing resolution of the same fact.
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
interface EnvironmentInterface
{
    public function has(string $name): bool;
}
