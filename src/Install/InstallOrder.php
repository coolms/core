<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * The order installers run in, DERIVED from what they declare rather than
 * assigned by a number.
 *
 * !! WHAT THIS IS NOT. It is not a priority sort. Ordering by a number orders by
 * where somebody put an installer: a new module has to guess one, and two equal
 * numbers fall back to whatever order the container yields -- which is the
 * alphabet, which is the defect this replaces. Nothing here reads a priority.
 *
 * !! A FALLBACK ORDER WOULD MAKE THIS WORTHLESS. A topological sort that shrugs
 * and returns an arbitrary order when it cannot sort is the old alphabet under a
 * new name, so this REFUSES instead -- see {@see UnorderableInstallersException}
 * -- and refuses BEFORE the first installer executes, which is the whole gain
 * over asserting a prerequisite at the point of use.
 *
 * The tie-break between installers that genuinely do not depend on each other is
 * the class name. That is not a hidden priority: two installers with no declared
 * relation are unordered BY DEFINITION, and picking a stable one keeps a run
 * reproducible. If their order actually matters, that is an undeclared
 * prerequisite and the fix is to declare it.
 *
 * Installers that do not implement {@see DeclaresPrerequisitesInterface} require
 * nothing and provide nothing. They still run -- the guard is the default, the
 * same way {@see VfsPathClaims} skips a non-declarer.
 */
final readonly class InstallOrder
{
    /**
     * @template T of object
     *
     * @param iterable<T> $installers
     *
     * @return list<T> the same installers, in an order that satisfies every declaration
     *
     * @throws UnorderableInstallersException when a requirement has no provider, or the
     *                                        declarations form a cycle
     */
    public static function sort(iterable $installers): array
    {
        /** @var list<object> $all */
        $all = [...$installers];

        // token => installers that provide it
        /** @var array<string, list<int>> $providers */
        $providers = [];
        foreach ($all as $i => $installer) {
            if (!$installer instanceof DeclaresPrerequisitesInterface) {
                continue;
            }
            foreach ($installer->declaredProvisions() as $token) {
                $providers[$token][] = $i;
            }
        }

        // Every unsatisfied requirement is collected before any is thrown. A
        // resolver that stops at the first lists FIRST failures, not failures,
        // and the operator fixes one prerequisite per run.
        /** @var array<string, list<class-string>> $unsatisfied */
        $unsatisfied = [];
        /** @var array<int, array<int, true>> $dependsOn */
        $dependsOn = [];

        foreach ($all as $i => $installer) {
            $dependsOn[$i] = [];
            if (!$installer instanceof DeclaresPrerequisitesInterface) {
                continue;
            }
            foreach ($installer->declaredRequirements() as $token) {
                if (!isset($providers[$token])) {
                    $unsatisfied[$token][] = $installer::class;

                    continue;
                }
                foreach ($providers[$token] as $p) {
                    // An installer may declare a token it also provides -- that
                    // is a statement about its own effect, not a dependency on
                    // itself, and treating it as one would invent a cycle.
                    if ($p !== $i) {
                        $dependsOn[$i][$p] = true;
                    }
                }
            }
        }

        if ([] !== $unsatisfied) {
            throw UnorderableInstallersException::unsatisfied($unsatisfied);
        }

        return self::topological($all, $dependsOn);
    }

    /**
     * Kahn's algorithm, with the ready set drained in class-name order so the
     * result is reproducible run to run.
     *
     * @param list<object>                 $all
     * @param array<int, array<int, true>> $dependsOn
     *
     * @return list<object>
     *
     * @throws UnorderableInstallersException
     */
    private static function topological(array $all, array $dependsOn): array
    {
        $remaining = array_keys($all);
        /** @var list<object> $ordered */
        $ordered = [];
        $done = [];

        while ([] !== $remaining) {
            $ready = [];
            foreach ($remaining as $i) {
                $blocked = false;
                foreach (array_keys($dependsOn[$i]) as $dep) {
                    if (!isset($done[$dep])) {
                        $blocked = true;
                        break;
                    }
                }
                if (!$blocked) {
                    $ready[] = $i;
                }
            }

            if ([] === $ready) {
                // Nothing can run, and installers are left: every one of them is
                // waiting on another that is also waiting. Refuse, naming them.
                throw UnorderableInstallersException::cycle(
                    array_values(array_map(static fn (int $i): string => $all[$i]::class, $remaining)),
                );
            }

            usort($ready, static fn (int $a, int $b): int => $all[$a]::class <=> $all[$b]::class);

            foreach ($ready as $i) {
                $ordered[] = $all[$i];
                $done[$i] = true;
            }
            $remaining = array_values(array_filter($remaining, static fn (int $i): bool => !isset($done[$i])));
        }

        return $ordered;
    }
}
