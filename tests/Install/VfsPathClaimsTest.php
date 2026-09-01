<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install;

use CoolMS\Core\Install\DeclaresVfsPathsInterface;
use CoolMS\Core\Install\VfsPathClaims;
use PHPUnit\Framework\TestCase;

/**
 * The collision check is tested directly rather than through `coolms:install`,
 * because a check reachable only by running an installation is one nobody can
 * point a failing case at.
 */
final class VfsPathClaimsTest extends TestCase
{
    public function testTwoModulesClaimingOnePathAreReported(): void
    {
        $a = $this->installer(['/media']);
        $b = $this->installer(['/media', '/docs']);

        $collisions = VfsPathClaims::collisions([$a, $b]);

        self::assertArrayHasKey('/media', $collisions);
        self::assertCount(2, $collisions['/media']);
        self::assertArrayNotHasKey(
            '/docs',
            $collisions,
            'a path only one installer claims is not a collision',
        );
    }

    public function testDistinctClaimsAreSilent(): void
    {
        $collisions = VfsPathClaims::collisions([
            $this->installer(['/media']),
            $this->installer(['/docs']),
            $this->installer(['/home']),
        ]);

        self::assertSame([], $collisions, 'a warning that fires on the normal case is a defect');
    }

    public function testATrailingSlashIsTheSameClaim(): void
    {
        $collisions = VfsPathClaims::collisions([
            $this->installer(['/media']),
            $this->installer(['/media/']),
        ]);

        self::assertArrayHasKey(
            '/media',
            $collisions,
            '/media and /media/ are the same directory; comparing them literally '
            . 'would let exactly this collision through',
        );
    }

    public function testOneInstallerRepeatingItselfIsNotACollision(): void
    {
        $collisions = VfsPathClaims::collisions([$this->installer(['/media', '/media/'])]);

        self::assertSame(
            [],
            $collisions,
            'that is a typo inside one module, not two modules disagreeing -- '
            . 'reporting it would make the check cry wolf',
        );
    }

    public function testInstallersThatDeclareNothingAreIgnored(): void
    {
        $plain = new class {
            public function installStructure(): void
            {
            }
        };

        $collisions = VfsPathClaims::collisions([$plain, $this->installer(['/media'])]);

        self::assertSame([], $collisions);
    }

    /** @param list<string> $paths */
    private function installer(array $paths): DeclaresVfsPathsInterface
    {
        return new class($paths) implements DeclaresVfsPathsInterface {
            /** @param list<string> $paths */
            public function __construct(private readonly array $paths)
            {
            }

            /** @return list<string> */
            public function declaredVfsPaths(): array
            {
                return $this->paths;
            }
        };
    }
}
