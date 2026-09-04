<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

use function array_keys;
use function hash;
use function implode;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Seed content without ever clobbering somebody's edit.
 *
 * ⚠️ ONE IMPLEMENTATION, because there are three callers -- the docs importer,
 * module installers and theme content seeding -- and three copies of this rule
 * will diverge until only one of them is right. It is deliberately pure: no
 * container, no database, no HTTP, so a standalone CLI tool and a domain service
 * can both use it.
 *
 * ## The rule, and why it is shaped this way
 *
 * The comparison is against the hash this seeder RECORDED, never against the
 * incoming bytes. That makes the question **"has a human changed this?"** rather
 * than "do these two differ?" -- the second is true every time a source file is
 * edited and would refuse exactly the updates a seeder exists to make.
 *
 * ⚠️ **No marker means REFUSE.** An artefact seeded before this guard existed,
 * or created by hand, has no baseline and an edit cannot be ruled out. Refusing
 * costs an operator one `--force`; assuming costs them their work.
 *
 * ⚠️ **And an occupied path is refused even when nothing is stored in the
 * locale being seeded.** "Nothing here in `uk`" is not "nothing here": a page
 * somebody wrote in `en` is a node this seeder does not own, and writing into it
 * would attach a second body to their page and merge a theme's title, order and
 * template over theirs. The question is whether the PATH is free, not whether
 * one body is missing -- a marker cannot answer that, because the thing it would
 * have to be on was never seeded.
 *
 * ## Provenance is the marker, never the slug
 *
 * The marker is keyed by SEEDER ID under `extras['seed']`, so:
 *
 *   - renaming a page does not make the seeder create a duplicate on its next
 *     run -- it knows the node is its own whatever it is now called;
 *   - two seeders can own different artefacts in the same tree without reading
 *     each other's markers;
 *   - "who made this" survives, which is what `navi` records as
 *     `meta['contributor']` and what module removal needed.
 *
 * Keying idempotency on a slug, id or name instead means a rename produces a
 * duplicate, and the person who renamed it will not connect the two events.
 */
final readonly class SeedGuard
{
    /** Where the marker lives on an artefact's `extras`. */
    public const string EXTRAS_KEY = 'seed';

    /**
     * @param string $seederId who is seeding -- a stable identifier such as
     *                         `theme:coolms-default` or `docs:concepts`, never
     *                         a slug or a title
     */
    public function __construct(
        private string $seederId,
    ) {
    }

    /**
     * ⚠️ `$extras` doubles as the OCCUPANCY signal: null means nothing is at
     * this path at all, and an array -- empty included -- means something is.
     * Implementations of {@see SeedTargetInterface} are required to answer that
     * way, because the difference decides whether a free path or somebody's page
     * is about to be written into.
     *
     * @param array<string, mixed>|null $extras       the artefact's stored extras, or null if the PATH is free
     * @param string|null               $liveBody     what is stored now in this locale, or null if there is none
     * @param string                    $incomingBody what this run would write
     */
    public function decide(?array $extras, ?string $liveBody, string $incomingBody, bool $force = false): SeedDecision
    {
        if (null === $liveBody) {
            // Nothing at the path at all, or a node this seeder owns whose body
            // in ANOTHER locale it is now adding. Both are its own to write.
            if (null === $extras || $this->owns($extras)) {
                return SeedDecision::Create;
            }

            // ⚠️ Occupied by something this seeder did not create. The absent
            // body makes this look like a free path and it is not.
            return $force ? SeedDecision::ForcedOverwrite : SeedDecision::RefuseOccupied;
        }

        $recorded = $this->recordedHash($extras);
        $live = hash('sha256', $liveBody);

        // Edited, or unprovable. Both are the operator's call, not ours.
        if (null === $recorded || $recorded !== $live) {
            return $force ? SeedDecision::ForcedOverwrite : SeedDecision::RefuseEdited;
        }

        return $live === hash('sha256', $incomingBody)
            ? SeedDecision::SkipUnchanged
            : SeedDecision::Overwrite;
    }

    /**
     * Has a human changed this since the seeder wrote it?
     *
     * The same rule as {@see decide()} without needing the incoming bytes --
     * which some callers do not have at the point they must decide whether to
     * proceed. The docs importer renders its Markdown through an API call and
     * would otherwise have to pay for that render on every page it is about to
     * skip.
     *
     * ⚠️ Returns true when there is no marker, for the reason given on the class:
     * absent proof is not proof of absence.
     *
     * @param array<string, mixed>|null $extras
     */
    public function isEdited(?array $extras, ?string $liveBody): bool
    {
        if (null === $liveBody) {
            return false;
        }

        $recorded = $this->recordedHash($extras);

        return null === $recorded || $recorded !== hash('sha256', $liveBody);
    }

    /**
     * Why {@see isEdited()} said yes, in words an operator can act on.
     *
     * Two different situations that both refuse, and the operator's next move
     * differs: one is somebody's work, the other is a missing baseline.
     *
     * @param array<string, mixed>|null $extras
     */
    public function reasonEdited(?array $extras): string
    {
        if (null !== $this->recordedHash($extras)) {
            return 'edited since the last seed';
        }

        // ⚠️ Naming the owner matters when a section has carried more than one
        // theme. "No marker" would be false -- there IS one, it belongs to
        // somebody else -- and it would send an operator looking for a hand edit
        // that never happened.
        $others = $this->otherSeeders($extras);

        return [] === $others
            ? 'no seed marker recorded -- cannot prove it is unedited'
            : sprintf('seeded by %s, not by this seeder', implode(', ', $others));
    }

    /**
     * Why a path was refused as occupied, in words an operator can act on.
     *
     * @param array<string, mixed>|null $extras
     */
    public function reasonOccupied(?array $extras): string
    {
        $others = $this->otherSeeders($extras);

        return [] === $others
            ? 'something already exists at this path that this seeder did not create'
            : sprintf('this path already holds content seeded by %s', implode(', ', $others));
    }

    /**
     * The reason for a decision that {@see SeedDecision::needsReporting()}.
     *
     * ⚠️ Needs `$liveBody` to tell the two refusals apart: an occupied path has
     * no stored body in this locale, which is exactly what an untouched new page
     * looks like from the extras alone.
     *
     * @param array<string, mixed>|null $extras
     */
    public function reasonFor(SeedDecision $decision, ?array $extras, ?string $liveBody): string
    {
        if (!$decision->needsReporting()) {
            return '';
        }

        return null === $liveBody && null !== $extras
            ? $this->reasonOccupied($extras)
            : $this->reasonEdited($extras);
    }

    /**
     * The marker to store, alongside whatever else the caller patches.
     *
     * ⚠️ Write it AFTER the body, never before: recorded first, it would claim a
     * state that a failed write never reached, and the next run would take that
     * claim as proof the artefact was untouched.
     *
     * @param array<string, mixed>|null $extras existing extras, so other seeders' markers survive
     *
     * @return array<string, mixed> the value for `extras['seed']`
     */
    public function marker(string $writtenBody, ?array $extras = null): array
    {
        $all = is_array($extras[self::EXTRAS_KEY] ?? null) ? $extras[self::EXTRAS_KEY] : [];
        $all[$this->seederId] = ['hash' => hash('sha256', $writtenBody)];

        return $all;
    }

    /**
     * Seeder ids on this artefact that are not ours.
     *
     * @param array<string, mixed>|null $extras
     *
     * @return list<string>
     */
    private function otherSeeders(?array $extras): array
    {
        $bag = $extras[self::EXTRAS_KEY] ?? null;
        if (!is_array($bag)) {
            return [];
        }

        $others = [];
        foreach (array_keys($bag) as $id) {
            if ($this->seederId !== $id) {
                $others[] = (string) $id;
            }
        }

        return $others;
    }

    /**
     * @param array<string, mixed> $extras
     */
    private function owns(array $extras): bool
    {
        return null !== $this->recordedHash($extras);
    }

    /**
     * @param array<string, mixed>|null $extras
     */
    private function recordedHash(?array $extras): ?string
    {
        if (null === $extras) {
            return null;
        }

        $bag = $extras[self::EXTRAS_KEY] ?? null;
        if (is_array($bag)) {
            $mine = $bag[$this->seederId] ?? null;
            if (is_array($mine) && is_string($mine['hash'] ?? null)) {
                return $mine['hash'];
            }
        }

        // ⚠️ The docs importer's existing flat marker. Read so that adopting
        // this guard does not refuse all twelve pages on its first run -- which
        // would be safe, correct, and would make the change look broken.
        return is_string($extras['importedHash'] ?? null) ? $extras['importedHash'] : null;
    }
}
