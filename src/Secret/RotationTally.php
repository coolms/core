<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * What one sweep over one kind of sealed value found, counted by READING
 * every value rather than by looking at markers: a marker names the format
 * a value was written in, never the key it was sealed under, so only an
 * attempt to open it can say which key that was -- or that neither does.
 *
 *  - `current`    opened under the current key: nothing to do
 *  - `previous`   opened under the previous key only: re-sealed when applying
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
