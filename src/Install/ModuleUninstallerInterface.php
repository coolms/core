<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * A module states how to undo what its installation put in place.
 *
 * SEPARATE from {@see ModuleInstallerInterface} and optional, on purpose. That
 * interface has 23 implementations, none of which extends a base class -- there
 * is no abstract installer and no trait -- so a method added there would break
 * every one of them and every third-party implementation.
 *
 * The cost is already visible on that interface: its `postInstall()` docblock
 * promises "a default implementation is a no-op", which a PHP interface cannot
 * give, so SIXTEEN of the 23 hand-write an empty method to satisfy it. Opting
 * in also makes what a module takes away explicit and greppable, and a module
 * that creates nothing has nothing to remove.
 *
 * REMOVE IS NOT PURGE. An implementation undoes what installation put in place
 * and deletes no data. If the only way to undo something is to delete a user's
 * rows, it belongs to purge and this method leaves it alone.
 *
 * MUST be idempotent, like install: running it twice is not an error.
 *
 * Tagged `coolms.module.uninstaller` -- auto-registered via DI
 * autoconfiguration, the same way installers are.
 */
interface ModuleUninstallerInterface
{
    /**
     * The module this removes, matching the bundle's `COMPONENT_NAME`.
     *
     * Stated rather than derived: an uninstaller does not have to live in the
     * bundle whose artefacts it removes, and deriving it from a namespace would
     * make that impossible to express.
     */
    public function moduleName(): string;

    /**
     * Undo what installation put in place.
     *
     * @param bool $dryRun report what would be undone and change nothing
     *
     * @return list<string> one line per thing undone, or that would be undone.
     *                      Empty means there was nothing to do, which is the
     *                      normal answer on a second run.
     */
    public function uninstall(bool $dryRun = false): array;
}
