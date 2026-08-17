<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * Each module that needs one-time DB/data installation implements this interface.
 *
 * Tagged with 'coolms.module.installer' -- auto-registered via DI autoconfiguration.
 * Called by `bin/console coolms:install` after the VFS structure is in place.
 *
 * MUST be idempotent -- safe to run multiple times.
 */
interface ModuleInstallerInterface
{
    public function install(): void;

    /**
     * Runs after all install() calls have completed.
     *
     * Override to perform work that depends on other installers having finished
     * (e.g. syncing derived data, setting ownership on nodes created by a peer installer).
     * Default implementation is a no-op.
     */
    public function postInstall(): void;
}
