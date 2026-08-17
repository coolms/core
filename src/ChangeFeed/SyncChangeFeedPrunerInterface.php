<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

use DateTimeImmutable;

/**
 * Deletes spent rows from the change-feed — the write half of the
 * feed, kept OUT of {@see SyncChangeFeedReaderInterface} on purpose: that port is
 * deliberately "a pure ordered window", and a reader that can also delete is a reader
 * every caller has to be trusted with.
 *
 * Core owns the feed but NOT the fleet, so it cannot decide WHAT is spent — that needs
 * `min(SyncEdge.cursor)`, and `SyncEdge` lives in the sync module (L2), which Core (L0) may
 * not import. So this port takes the floor as an ARGUMENT and stays dumb; the policy
 * lives with the module that owns the edges
 * (the sync module's own change-retention pruner). Same shape as
 * {@see \CoolMS\Core\Backup\TableBackupPortInterface} — Core supplies the mechanism,
 * a higher module supplies the meaning.
 *
 * DBAL-backed by necessity: `seq` is a DB-generated identity column, intentionally
 * unmapped on {@see SyncChange}, so the ORM cannot express these predicates.
 */
interface SyncChangeFeedPrunerInterface
{
    /**
     * Delete every change that is BOTH acked and aged; returns rows deleted.
     *
     * @param ?int              $ackedThroughSeq the highest `seq` every edge has acked —
     *                                           rows above it are still owed to someone.
     *                                           **NULL means "no edge constrains this"**
     *                                           (no edges registered), NOT "seq 0": the
     *                                           caller has decided the feed has no reader
     *                                           to protect, so age alone governs
     * @param DateTimeImmutable $recordedBefore  age floor, read from `recorded_at` —
     *                                           which exists for exactly this and is NOT
     *                                           the cursor (second precision; `seq` is
     *                                           the order)
     */
    public function deletePrunable(?int $ackedThroughSeq, DateTimeImmutable $recordedBefore): int;

    /** Dry-run counterpart of {@see deletePrunable()} — same predicate, no delete. */
    public function countPrunable(?int $ackedThroughSeq, DateTimeImmutable $recordedBefore): int;
}
