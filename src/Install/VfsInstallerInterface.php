<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * Each module that needs VFS directories implements this interface.
 *
 * Tagged with 'coolms.vfs.installer' -- auto-registered via DI autoconfiguration.
 * Called by `bin/console coolms:install` in dependency order.
 *
 * Adds nothing to {@see StructureInstallerInterface}: it exists so the tag has a
 * VFS-named contract to autoconfigure on, while the kernel types against Core's.
 *
 * MUST be idempotent -- safe to run multiple times.
 */
interface VfsInstallerInterface extends StructureInstallerInterface
{
}
