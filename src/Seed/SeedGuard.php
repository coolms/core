<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

use function array_key_exists;
use function array_keys;
use function array_values;
use function hash;
use function implode;
use function is_array;
use function is_string;
use function json_encode;
use function ksort;
use function sort;
use function sprintf;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

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
     * @param array<string, mixed>|null $extras         the artefact's stored extras, or null if the PATH is free
     * @param string|null               $liveBody       what is stored now in this locale, or null if there is none
     * @param string                    $incomingBody   what this run would write
     * @param array<string, mixed>      $incomingExtras the extras this run would merge -- a landing page's
     *                                                  sections live here, so a run that changes only these
     *                                                  is a real change
     */
    public function decide(
        ?array $extras,
        ?string $liveBody,
        string $incomingBody,
        bool $force = false,
        array $incomingExtras = [],
    ): SeedDecision {
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

        // Edited, or unprovable. Both are the operator's call, not ours.
        if ($this->isEdited($extras, $liveBody)) {
            return $force ? SeedDecision::ForcedOverwrite : SeedDecision::RefuseEdited;
        }

        $sameBody = hash('sha256', $liveBody) === hash('sha256', $incomingBody);

        // ⚠️ Compared over the INCOMING keys, which is the right set here: the
        // question is "would this run change anything?", and a key this run does
        // not write is not this run's business. That differs from the edit check
        // above, which must use the RECORDED keys -- see {@see isEdited()}.
        $incomingKeys = $this->sortedKeys($incomingExtras);
        $sameExtras = $this->fingerprint($incomingExtras, $incomingKeys)
            === $this->fingerprint($extras ?? [], $incomingKeys);

        return $sameBody && $sameExtras
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
     * ⚠️ **A page's content is not always its body.** A landing page keeps its
     * sections in `extras['blocks']`, so a guard that watched only the body
     * called such a page unedited however much an author had rearranged it --
     * and the next body change then rewrote the extras and discarded that work.
     * So the marker records the extras keys it wrote, and this compares a
     * fingerprint of the live values over exactly those keys.
     *
     * ⚠️ The RECORDED keys, never the current run's. Fingerprinting the live
     * side over the current keys was tried first and cannot work: the recorded
     * hash covers what the PREVIOUS run wrote, so a run whose key set changed
     * can never match and reports every page as edited. `VfsSeedTargetTest`
     * caught exactly that.
     *
     * @param array<string, mixed>|null $extras
     */
    public function isEdited(?array $extras, ?string $liveBody): bool
    {
        if (null === $liveBody) {
            return false;
        }

        $marker = $this->recordedMarker($extras);
        if (null === $marker || !is_string($marker['hash'] ?? null)) {
            return true;
        }

        if ($marker['hash'] !== hash('sha256', $liveBody)) {
            return true;
        }

        $keys = $this->recordedKeys($marker);

        // ⚠️ A marker written before the keys were recorded cannot answer for
        // the extras, and refusing every page seeded by an earlier version would
        // be safe, correct, and would make this change look broken. Same
        // precedent as the flat `importedHash` read below: fall back to the body
        // alone, and record the keys on the next write.
        if (null === $keys) {
            return false;
        }

        return ($marker['extras'] ?? null) !== $this->fingerprint($extras ?? [], $keys);
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
     * ⚠️ It records WHICH EXTRAS KEYS it wrote, not just the body hash. Those
     * keys are what {@see isEdited()} fingerprints the live values over, so a
     * landing page whose sections an author rearranged is recognised as edited
     * instead of being quietly rewritten on the next body change.
     *
     * The marker key itself is never among them: it is provenance, not content,
     * and including it would make the fingerprint depend on itself.
     *
     * @param array<string, mixed>|null $extras        existing extras, so other seeders' markers survive
     * @param array<string, mixed>      $writtenExtras the extras being written with this body
     *
     * @return array<string, mixed> the value for `extras['seed']`
     */
    public function marker(string $writtenBody, ?array $extras = null, array $writtenExtras = []): array
    {
        $all = is_array($extras[self::EXTRAS_KEY] ?? null) ? $extras[self::EXTRAS_KEY] : [];

        $keys = $this->sortedKeys($writtenExtras);
        $all[$this->seederId] = [
            'hash' => hash('sha256', $writtenBody),
            'keys' => $keys,
            'extras' => $this->fingerprint($writtenExtras, $keys),
        ];

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
        $marker = $this->recordedMarker($extras);

        return is_string($marker['hash'] ?? null) ? $marker['hash'] : null;
    }

    /**
     * This seeder's marker on the artefact, normalised.
     *
     * @param array<string, mixed>|null $extras
     *
     * @return array<string, mixed>|null
     */
    private function recordedMarker(?array $extras): ?array
    {
        if (null === $extras) {
            return null;
        }

        $bag = $extras[self::EXTRAS_KEY] ?? null;
        if (is_array($bag)) {
            $mine = $bag[$this->seederId] ?? null;
            if (is_array($mine) && is_string($mine['hash'] ?? null)) {
                return $mine;
            }
        }

        // ⚠️ The docs importer's existing flat marker. Read so that adopting
        // this guard does not refuse all twelve pages on its first run -- which
        // would be safe, correct, and would make the change look broken. It
        // carries no key list, which {@see isEdited()} treats as "cannot answer
        // for the extras" rather than as "edited".
        return is_string($extras['importedHash'] ?? null)
            ? ['hash' => $extras['importedHash']]
            : null;
    }

    /**
     * The extras keys a marker recorded, or null when it recorded none.
     *
     * @param array<string, mixed> $marker
     *
     * @return list<string>|null
     */
    private function recordedKeys(array $marker): ?array
    {
        $keys = $marker['keys'] ?? null;
        if (!is_array($keys)) {
            return null;
        }

        $clean = [];
        foreach ($keys as $key) {
            if (is_string($key)) {
                $clean[] = $key;
            }
        }

        return $clean;
    }

    /**
     * @param array<string, mixed> $source
     *
     * @return list<string>
     */
    private function sortedKeys(array $source): array
    {
        $keys = [];
        foreach (array_keys($source) as $key) {
            // The marker is provenance, not content. It is written INTO the same
            // bag, so leaving it in would make the fingerprint cover itself.
            if (self::EXTRAS_KEY !== $key) {
                $keys[] = (string) $key;
            }
        }
        sort($keys);

        return $keys;
    }

    /**
     * A stable hash of `$source` restricted to `$keys`.
     *
     * ⚠️ Only the SELECTED keys are sorted, and nested values are left in the
     * order they were stored. A landing page's `blocks` is a list whose order is
     * the order of the sections on the page -- sorting it would make a rearranged
     * page fingerprint identical to the original, which is the exact edit this
     * exists to notice.
     *
     * ⚠️ A key that was recorded and is now MISSING changes the fingerprint,
     * because it is absent from the subset. That is correct: deleting a key is
     * an edit.
     *
     * @param array<string, mixed> $source
     * @param list<string>         $keys
     */
    private function fingerprint(array $source, array $keys): string
    {
        $subset = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $source)) {
                $subset[$key] = $source[$key];
            }
        }
        ksort($subset);

        return hash(
            'sha256',
            json_encode($subset, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
