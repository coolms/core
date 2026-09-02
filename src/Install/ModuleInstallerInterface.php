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
     * Runs after every install() has completed.
     *
     * For work that depends on other installers having finished -- syncing
     * derived data, taking ownership of nodes a peer installer created.
     *
     * This is a REQUIRED method: an interface carries no body, so there is no
     * default to inherit and nothing to override. An installer with no
     * post-install work declares that by using {@see PostInstallNoop} rather
     * than by writing an empty body.
     */
    public function postInstall(): void;
}
