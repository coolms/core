<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

use DateTimeImmutable;

/**
 * A read-model row projected from the controller→edge change-feed: one
 * captured row-change, carrying the monotonic {@see $seq} cursor an edge advances through.
 *
 * Distinct from the {@see SyncChange} write-entity. The entity is what the capture
 * listener PERSISTS — it has no `seq` (the DB assigns it via an identity column). This
 * VO is what {@see SyncChangeFeedReaderInterface} reads back out of `coolms_sync_changes`
 * for delivery, so `seq` is only ever known on the read side.
 */
final class SyncChangeDelta
{
    public function __construct(
        public int $seq,
        public string $tableName,
        public string $rowId,
        public SyncChangeOp $op,
        public DateTimeImmutable $recordedAt,
    ) {
    }
}
