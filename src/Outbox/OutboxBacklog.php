<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

/**
 * The relay's liveness, as numbers: how many outbox rows await delivery, how
 * many of them have waited longer than the caller's threshold -- the number
 * that should be zero while a relay is running -- and when the oldest of them
 * was written.
 */
final readonly class OutboxBacklog
{
    public function __construct(
        public int $unpublished,
        public int $staleUnpublished,
        public ?DateTimeImmutable $oldestUnpublishedAt,
    ) {
    }

    public function isHealthy(): bool
    {
        return 0 === $this->staleUnpublished;
    }
}
