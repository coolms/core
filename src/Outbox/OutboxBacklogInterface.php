<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

/**
 * Read port over the undelivered half of `coolms_outbox`. A relay that stops
 * is as silent as a relay that was never started, so an operator needs a
 * number that should be zero: rows still unpublished after `$olderThan`.
 * One indexed count (the `published_at IS NULL` partition) plus the oldest
 * row's timestamp; cheap enough for a monitor to ask every minute.
 */
interface OutboxBacklogInterface
{
    public function unpublishedBacklog(DateTimeImmutable $olderThan): OutboxBacklog;
}
