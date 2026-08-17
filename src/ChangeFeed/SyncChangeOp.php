<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * The row-level operation a {@see SyncChange} records for the controller→edge sync
 * change-feed. Deliberately only TWO cases — the edge applies changes
 * via the idempotent upsert-by-UUID + delete-by-id primitives ({@see \CoolMS\Core\Backup\TableBackupPortInterface}),
 * so an INSERT and an UPDATE are the same edge operation (`upsert`); only a DELETE is
 * distinct (a snapshot-diff can't see deletes — the feed's explicit delete op is how
 * they propagate).
 */
enum SyncChangeOp: string
{
    case Upsert = 'upsert';

    case Delete = 'delete';
}
