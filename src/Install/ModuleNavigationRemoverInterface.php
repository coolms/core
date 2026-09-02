<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * Takes back the navigation entries a module contributed.
 *
 * A PORT because the teardown lives in core-bundle, beside the install command,
 * and the navigation graph is application code. The application supplies the
 * implementation; nothing here knows how a menu is stored.
 *
 * Navigation is the one artefact kind a removal can actually undo, and the
 * reason is that navi records the owner ON THE ROW -- so what belongs to a
 * module is knowable without the module's help. Everything else an installation
 * creates either leaves with the bundle or has no recorded owner at all.
 */
interface ModuleNavigationRemoverInterface
{
    /**
     * @param bool $dryRun count what would be removed and change nothing
     *
     * @return int nodes removed, or that would be
     */
    public function unseedModule(string $moduleName, bool $dryRun = false): int;
}
