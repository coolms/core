<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * What one sweep over one kind of sealed value found, counted by OPENING
 * every value rather than by reading its marker.
 *
 * !! This used to say a marker "names the format a value was written in,
 * never the key it was sealed under". That stopped being true when the
 * current form became `enc:v2:<key id>:...`, which does name its key, and
 * the sentence then hid the actual reason: the id is a HINT and the box is
 * the PROOF. A sealer that meets a named key which fails tries the others,
 * so a value whose marker names the current key but opens under the previous
 * one exists, and it is precisely the value a rotation must not skip. The
 * two older forms (`enc:v1:` and bare base64) name no key at all. Counting
 * by opening is therefore not a limitation being worked around; it is the
 * only count the operator can act on.
 *
 *  - `current`    opened under the current key: nothing to do
 *  - `previous`   opened under the previous key only: re-sealed when applying,
 *                 and the number that must reach zero in every kind before the
 *                 previous key leaves the ring
 *  - `unreadable` opened under neither: left as it is, reported, because a
 *                 rotation cannot repair a value nobody's key opens
 *  - `plaintext`  not sealed at all (a kind that was encrypted opportunistically
 *                 may still hold values from before that); the rotation leaves
 *                 them, the kind's own backfill seals them
 *  - `resealed`   how many of `previous` were actually rewritten this run
 */
final readonly class RotationTally
{
    public function __construct(
        public int $current = 0,
        public int $previous = 0,
        public int $unreadable = 0,
        public int $plaintext = 0,
        public int $resealed = 0,
    ) {
    }

    public function total(): int
    {
        return $this->current + $this->previous + $this->unreadable + $this->plaintext;
    }

    /** The window is closed for this kind when nothing is left under the previous key. */
    public function closed(): bool
    {
        return 0 === $this->previous;
    }

    public function add(self $other): self
    {
        return new self(
            $this->current + $other->current,
            $this->previous + $other->previous,
            $this->unreadable + $other->unreadable,
            $this->plaintext + $other->plaintext,
            $this->resealed + $other->resealed,
        );
    }
}
