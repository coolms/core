<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * The no-op second phase, for an installer that has no work to do after its
 * peers have finished.
 *
 * `ModuleInstallerInterface::postInstall()` is a required method, and a PHP
 * interface cannot carry a body -- so every installer with nothing to do there
 * had to hand-write an empty one, and 16 of them did. The docblock meanwhile
 * described a "default implementation" the language never provided. This trait
 * is that default, made real: `use PostInstallNoop;` says the module has no
 * post-install step, which an empty method body only implies.
 */
trait PostInstallNoop
{
    public function postInstall(): void
    {
    }
}
