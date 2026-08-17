<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * The outcome of applying a batch of change-feed deltas to the local DB.
 * `$highestSeq` is the greatest `seq` seen in the applied batch — the caller (the edge
 * pull loop, B.2.4c) uses it to advance its cursor, subject to the commit-ordering
 * safe-watermark. `$upserted`/`$deleted` are row counts for reporting.
 */
final class SyncApplyResult
{
    public function __construct(
        public int $upserted,
        public int $deleted,
        public int $highestSeq,
    ) {
    }
}
