<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

/**
 * Implemented by any module that needs a non-loginable system user.
 *
 * Register implementations with the DI tag `coolms.system_user`.
 */
interface SystemUserProviderInterface
{
    /** Username and primary group name — singular snake_case. */
    public string $systemUsername { get; }

    /**
     * Human-readable display name for the profile.
     * Example: 'Media Library', 'CMS Page'.
     */
    public string $displayName { get; }

    /** Module alias used for registry lookup — unique across all providers. */
    public string $moduleAlias { get; }
}
