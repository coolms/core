<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * Records change-feed rows for writers that BYPASS the UnitOfWork.
 *
 * The persistence adapter's change-capture listener covers
 * the normal path — anything that goes through `persist`/`flush` is captured for free, and
 * code should keep relying on that. This port exists for the writes the listener
 * structurally cannot see: bulk DQL and raw DBAL that change synced rows without ever
 * scheduling an entity. Those are invisible to `onFlush`, so without an explicit record
 * they are **silent edge drift** — the exact §3 failure mode the feed exists to prevent.
 *
 * **Use this ONLY when the write really does bypass the UoW.** Calling it for an ordinary
 * ORM write would double-record the row. Duplicates are harmless to correctness (the
 * applier coalesces per `(table,row)` and upserts idempotently) but they are noise in a
 * log that a retention pruner has to carry, so don't.
 *
 * A write to a table outside the synced universe is a NO-OP, not an error: the caller
 * shouldn't have to know the sync topology to do its job, and the registry is the one
 * place that answers "is this table synced?".
 */
interface SyncChangeRecorderInterface
{
    /**
     * Record that `$rowIds` in `$table` now hold new data an edge must re-fetch. Returns
     * the number of rows actually recorded (0 when the table isn't synced, or `$rowIds`
     * is empty).
     *
     * **Call it in the SAME transaction as the write it describes**, so the record and the
     * change commit together or neither does — the atomicity the capture listener gets
     * from `onFlush` firing inside the flush's transaction. A `preUpdate` caller already
     * satisfies this (the ORM's commit transaction is open by then).
     *
     * @param list<string> $rowIds
     */
    public function recordUpserts(string $table, array $rowIds): int;
}
