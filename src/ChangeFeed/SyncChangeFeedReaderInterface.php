<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * Reads the controller→edge change-feed after a cursor — the READ half of
 * the CDC spine whose WRITE half is the persistence adapter's
 * change-capture listener. An
 * edge polls "give me the changes after cursor N", replays each (upsert/delete by row
 * UUID) and converges. This port lives in the Domain so the sync delivery surface
 * (the sync module) can depend on it without reaching into the persistence
 * adapter; the concrete DBAL projection ships in `coolms/core-doctrine`.
 *
 * **Cursor = the monotonic `seq`** — a DB-level `BIGINT GENERATED ALWAYS AS IDENTITY`,
 * intentionally unmapped on the entity ({@see SyncChange}). Ordering by `seq` gives a
 * stable total order for pagination that the coarse, second-precision `recorded_at`
 * cannot.
 *
 * **Correctness boundary (deliberately closed in the APPLY/ack slice, B.2.4):** the reader
 * returns only COMMITTED rows — an uncommitted insert is invisible under READ COMMITTED —
 * in `seq` order, which is correct for total-order + pagination. It does not by itself
 * defeat the classic CDC commit-ordering race: a transaction assigned a lower `seq` that
 * commits AFTER a higher-`seq` one can be skipped if a consumer naively advances its
 * cursor past the gap. That is a cursor-ADVANCEMENT concern, resolved where the cursor is
 * actually persisted (the edge): idempotent replay makes any re-delivery harmless, and a
 * bounded lagged re-scan floor catches late commits. Keeping the reader a pure window
 * means that single mitigation lives in exactly one place.
 */
interface SyncChangeFeedReaderInterface
{
    public const int DEFAULT_LIMIT = 500;

    /**
     * Changes with `seq` strictly greater than `$afterSeq`, ascending by `seq`, at most
     * `$limit` rows (clamped by the implementation to a sane maximum).
     *
     * @return list<SyncChangeDelta>
     */
    public function readAfter(int $afterSeq, int $limit = self::DEFAULT_LIMIT): array;
}
