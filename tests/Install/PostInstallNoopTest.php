<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install;

use CoolMS\Core\Tests\Install\Fixture\InstallerWithNoPostInstallStep;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * `PostInstallNoop` is the default second phase for an installer that has
 * nothing to do once its peers have finished: `use PostInstallNoop;` states
 * that, where an empty method body only implies it.
 *
 * The fixture is the consumer's shape: an installer whose `install()` does
 * work and whose `postInstall()` comes from the trait.
 */
final class PostInstallNoopTest extends TestCase
{
    #[Test]
    public function theSecondPhaseDoesNothingAndLeavesTheInstallerAsItWas(): void
    {
        $installer = new InstallerWithNoPostInstallStep();
        $installer->install();
        $before = clone $installer;

        $installer->postInstall();
        $installer->postInstall();

        self::assertSame(1, $installer->installRuns, 'the second phase must not run the first one again');
        self::assertEquals($before, $installer);
    }
}
