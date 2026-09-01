<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * Which VFS paths more than one installer claims.
 *
 * Pure, and separate from the console command that prints it, so the detection
 * can be tested without running an installation: a check exercised only through
 * its caller is a check nobody can point a failing case at.
 */
final readonly class VfsPathClaims
{
    /**
     * Paths claimed by two or more installers, each with the declaring classes.
     *
     * A path is normalised before comparison -- `/media` and `/media/` are the
     * same claim, and a module that writes one while another writes the other
     * would otherwise collide silently, which is the case this exists to catch.
     *
     * @param iterable<object> $installers
     *
     * @return array<string, list<class-string>> path => declaring classes, sorted
     */
    public static function collisions(iterable $installers): array
    {
        // Keyed by object id, not by class name. "Two installers" means two
        // objects: deduplicating by class would collapse two distinct
        // installers that happen to share a class -- which is exactly what an
        // anonymous class does -- and report no collision at all.
        /** @var array<string, array<int, class-string>> $claims */
        $claims = [];

        foreach ($installers as $installer) {
            if (!$installer instanceof DeclaresVfsPathsInterface) {
                continue;
            }

            foreach ($installer->declaredVfsPaths() as $path) {
                $normalised = self::normalise($path);
                if ('' === $normalised) {
                    continue;
                }

                // The same installer naming a path twice is a typo in one
                // module, not two modules disagreeing -- which is what this
                // reports. Counting it would make the report cry wolf, and the
                // object id is what keeps the two cases apart.
                $claims[$normalised][spl_object_id($installer)] = $installer::class;
            }
        }

        $collisions = [];
        foreach ($claims as $path => $owners) {
            if (count($owners) < 2) {
                continue;
            }

            $names = array_values($owners);
            sort($names);
            $collisions[$path] = $names;
        }

        ksort($collisions);

        return $collisions;
    }

    /** Trailing slashes removed; the root itself stays `/`. */
    private static function normalise(string $path): string
    {
        $trimmed = rtrim(trim($path), '/');

        return '' === $trimmed && str_starts_with(trim($path), '/') ? '/' : $trimmed;
    }
}
