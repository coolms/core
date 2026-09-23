<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use function max;

/**
 * What one kind still holds that the key ring CANNOT OPEN.
 *
 * {@see RotationTally} counts VALUES -- the things the application reads and
 * writes through its own read path. This counts COPIES: the same ciphertext
 * kept somewhere else by a subsystem with its own reasons. The two are
 * different numbers and a rotation is not finished while either is above zero.
 *
 * **Why the distinction earns a second tally.** On 2026-09-23 a rotation
 * re-sealed 5,679 message bodies and reported `previous 0` in every kind,
 * truthfully: no VALUE was left under the retired key. 563.9 MB of ciphertext
 * that key opens was still on disk, held by file-history revisions, and
 * therefore also inside every backup bundle and every sync snapshot taken
 * since the bodies were first sealed. "Window closed" was a statement about
 * the application, not about the estate.
 *
 * ## Counted by opening, never by marker
 *
 * `retained` is the number of copies the ring was ASKED to open and could not.
 * The stored form names its key (`enc:v2:<key id>:`), and that id is a HINT --
 * {@see \CoolMS\Core\Bundle\Secret\KeyRingSealer} tries the other keys when the
 * named one fails, so a marker-only count is a count of claims. A marker may be
 * used to FIND candidates cheaply; the box decides.
 *
 * ## A zero is earned, not assumed
 *
 * `examined` is the denominator. A kind reporting `0 of 0` has established
 * nothing, and the difference between that and `0 of 6,821` is the whole value
 * of the number -- which is why {@see noCopyKept()} exists separately rather
 * than being spelled as a counted zero.
 *
 * ## And it says what it cannot see
 *
 * `blindSpot` is never empty. A residue count over a byte store does not see
 * database dumps, backup bundles a peer holds, or a disk image; a count over a
 * column does not see the row versions the database keeps until it vacuums.
 * A zero that is silent about its own scope is the defect this whole seam
 * exists to catch, one level up.
 *
 * ## Growth
 *
 * {@see SealedResidueInterface} is a published ONE-METHOD contract and stays
 * one method: a new fact about residue is a new optional member here, which
 * breaks no implementer, exactly as {@see \CoolMS\Core\Health\DependencyState}
 * is the growth point for {@see \CoolMS\Core\Health\LivenessProbeInterface}.
 */
final readonly class ResidueTally
{
    private function __construct(
        /** Copies the ring was asked to open and could not. The number that must be zero. */
        public int $retained,
        /** How many copies were asked. The denominator that makes a zero mean something. */
        public int $examined,
        /** Where a copy could still be and this count did not look. Never empty. */
        public string $blindSpot,
        /** False when nothing was walked because the kind keeps no copy by construction. */
        public bool $walked,
    ) {
    }

    /**
     * $examined copies were handed to the ring; $retained of them did not open.
     *
     * @param int $examined how many copies were asked -- the denominator
     * @param int $retained how many of them the ring could not open
     */
    public static function counted(int $examined, int $retained, string $blindSpot): self
    {
        return new self(max(0, $retained), max(0, $examined), $blindSpot, true);
    }

    /**
     * The kind keeps no copy by construction: the value is overwritten where it
     * stands, so there is no second place to walk.
     *
     * This is NOT a counted zero and does not pretend to be one -- `examined` is
     * 0 and `walked` is false. It is still an answer rather than silence,
     * because the blind spot is where an old copy genuinely does survive: the
     * row versions a database keeps until it vacuums, and every dump taken
     * before the rotation.
     */
    public static function noCopyKept(string $blindSpot): self
    {
        return new self(0, 0, $blindSpot, false);
    }

    /** Nothing of this kind is left that the ring cannot open. */
    public function closed(): bool
    {
        return 0 === $this->retained;
    }
}
