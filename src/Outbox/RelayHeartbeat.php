<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

/**
 * One pass of the relay, recorded by the relay itself: when it ran, how large
 * a batch it asked for, and how many rows it published -- zero included,
 * because a pass over an empty queue is exactly the pass a monitor needs to
 * see. Until this existed, a relay that had stopped read the same as one
 * with nothing to do.
 */
final readonly class RelayHeartbeat
{
    public function __construct(
        public DateTimeImmutable $at,
        public int $batch,
        public int $published,
    ) {
    }

    public function ageInSeconds(DateTimeImmutable $now): int
    {
        return $now->getTimestamp() - $this->at->getTimestamp();
    }
}
