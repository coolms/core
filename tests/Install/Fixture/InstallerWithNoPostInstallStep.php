<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install\Fixture;

use CoolMS\Core\Install\ModuleInstallerInterface;
use CoolMS\Core\Install\PostInstallNoop;

/**
 * An installer with work to do in `install()` and none after its peers have
 * finished, written the way a consumer writes one: it says so with the trait
 * rather than with an empty method of its own.
 */
final class InstallerWithNoPostInstallStep implements ModuleInstallerInterface
{
    use PostInstallNoop;

    public int $installRuns = 0;

    public function install(): void
    {
        ++$this->installRuns;
    }
}
