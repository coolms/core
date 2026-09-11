<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install;

use CoolMS\Core\Install\DeclaresPrerequisitesInterface;
use CoolMS\Core\Install\InstallOrder;
use CoolMS\Core\Install\UnorderableInstallersException;
use CoolMS\Core\Tests\Install\Fixture\AlphaRequiresOmega;
use CoolMS\Core\Tests\Install\Fixture\OmegaProvides;
use PHPUnit\Framework\TestCase;

/**
 * The ordering is tested directly rather than through `coolms:install`, for the
 * same reason the collision check is: a check reachable only by running an
 * installation is one nobody can point a failing case at.
 */
final class InstallOrderTest extends TestCase
{
    /**
     * !! THE TEST THE MECHANISM IS WORTHLESS WITHOUT.
     *
     * Two installers whose alphabetical order is WRONG: Alpha requires what
     * Omega provides. The old behaviour -- a plain `foreach` over a tagged
     * iterator yielding registration order, which is alphabetical -- runs Alpha
     * first and fails. This must run Omega then Alpha.
     *
     * The second assertion is what stops this testing nothing: it states that
     * the answer is NOT the input order, so a `sort()` that returned its
     * argument unchanged would fail here rather than pass quietly.
     */
    public function testAnInstallerRunsAfterTheOneProvidingWhatItRequires(): void
    {
        $alpha = new AlphaRequiresOmega();
        $omega = new OmegaProvides();

        // Fed in the order the unsorted iterator would yield: alphabetical.
        $input = [$alpha, $omega];
        self::assertSame(
            ['CoolMS\Core\Tests\Install\Fixture\AlphaRequiresOmega', 'CoolMS\Core\Tests\Install\Fixture\OmegaProvides'],
            array_map(static fn (object $i): string => $i::class, $input),
            'the fixtures must be fed in alphabetical order, or this proves nothing',
        );

        $ordered = InstallOrder::sort($input);

        self::assertSame([$omega, $alpha], $ordered, 'the provider must run first');
        self::assertNotSame(
            $input,
            $ordered,
            'the order was returned unchanged -- an unsorted foreach would pass this test',
        );
    }

    public function testARequirementNothingProvidesRefusesBeforeAnythingRuns(): void
    {
        $needy = $this->installer(['system-user:root'], []);

        try {
            InstallOrder::sort([$needy]);
            self::fail('an unsatisfiable prerequisite must refuse');
        } catch (UnorderableInstallersException $e) {
            self::assertArrayHasKey('system-user:root', $e->unsatisfied);
            self::assertSame([$needy::class], $e->unsatisfied['system-user:root'], 'the asker is named');
            self::assertSame([], $e->cycle, 'this is not a cycle and must not be reported as one');
            self::assertStringContainsString('nothing provides "system-user:root"', $e->getMessage());
        }
    }

    /**
     * A resolver that stops at the first missing prerequisite lists FIRST
     * failures, not failures, and the operator fixes one per run.
     */
    public function testEveryUnsatisfiedRequirementIsReportedNotOnlyTheFirst(): void
    {
        try {
            InstallOrder::sort([
                $this->installer(['system-user:root'], []),
                $this->installer(['vfs-node:/'], []),
            ]);
            self::fail('expected a refusal');
        } catch (UnorderableInstallersException $e) {
            self::assertCount(2, $e->unsatisfied);
            self::assertArrayHasKey('system-user:root', $e->unsatisfied);
            self::assertArrayHasKey('vfs-node:/', $e->unsatisfied);
        }
    }

    public function testACycleRefusesAndNamesEveryInstallerInIt(): void
    {
        $a = $this->installer(['token:b'], ['token:a']);
        $b = $this->installer(['token:a'], ['token:b']);

        try {
            InstallOrder::sort([$a, $b]);
            self::fail('a cycle must refuse rather than fall back to an arbitrary order');
        } catch (UnorderableInstallersException $e) {
            self::assertCount(2, $e->cycle, 'both installers are named, not just the first');
            self::assertContains($a::class, $e->cycle);
            self::assertContains($b::class, $e->cycle);
            self::assertSame([], $e->unsatisfied, 'a cycle is not an unsatisfied requirement');
        }
    }

    /**
     * The guard is the default: an installer that declares nothing is not
     * excluded, exactly as VfsPathClaims skips a non-declarer rather than
     * refusing it.
     */
    public function testAnInstallerThatDeclaresNothingStillRuns(): void
    {
        $plain = new class {};
        $omega = new OmegaProvides();

        $ordered = InstallOrder::sort([$plain, $omega]);

        self::assertCount(2, $ordered);
        self::assertContains($plain, $ordered);
    }

    /**
     * Declaring a token on both sides is a statement about the installer's own
     * effect, not a dependency on itself. Treating it as one would invent a
     * cycle out of a correct declaration.
     */
    public function testAnInstallerMayRequireAndProvideTheSameTokenWithoutACycle(): void
    {
        $both = $this->installer(['token:x'], ['token:x']);

        $ordered = InstallOrder::sort([$both]);

        self::assertSame([$both], $ordered);
    }

    /**
     * Two installers with no declared relation are unordered by definition. The
     * tie-break exists so a run is reproducible, not to encode a priority.
     */
    public function testIndependentInstallersComeBackInTheSameOrderEveryTime(): void
    {
        $make = fn (): array => [
            $this->installer([], ['token:one']),
            $this->installer([], ['token:two']),
            new OmegaProvides(),
        ];

        $first = array_map(static fn (object $i): string => $i::class, InstallOrder::sort($make()));
        $second = array_map(static fn (object $i): string => $i::class, InstallOrder::sort($make()));

        self::assertSame($first, $second);
    }

    /**
     * A three-deep chain, fed in an order that is wrong at every step, so the
     * result cannot be the input by accident.
     */
    public function testAChainIsOrderedEndToEnd(): void
    {
        $third = $this->installer(['token:second'], []);
        $second = $this->installer(['token:first'], ['token:second']);
        $first = $this->installer([], ['token:first']);

        $ordered = InstallOrder::sort([$third, $second, $first]);

        self::assertSame([$first, $second, $third], $ordered);
    }

    /**
     * @param list<string> $requires
     * @param list<string> $provides
     */
    private function installer(array $requires, array $provides): DeclaresPrerequisitesInterface
    {
        return new class($requires, $provides) implements DeclaresPrerequisitesInterface {
            /**
             * @param list<string> $requires
             * @param list<string> $provides
             */
            public function __construct(
                private readonly array $requires,
                private readonly array $provides,
            ) {
            }

            public function declaredRequirements(): array
            {
                return $this->requires;
            }

            public function declaredProvisions(): array
            {
                return $this->provides;
            }
        };
    }
}
