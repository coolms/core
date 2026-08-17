<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

/**
 * The relay's read/claim port over `coolms_outbox` (Core L0, F7). `claimUnpublished`
 * row-locks a batch with `FOR UPDATE SKIP LOCKED` so concurrent relay workers
 * never publish the same row twice — it MUST therefore be called inside a
 * transaction that stays open until the rows are marked (the relay command wraps
 * the whole publish-and-mark batch in one tx).
 */
interface OutboxRelayRepositoryInterface
{
    /**
     * Claim (and row-lock) up to `$limit` undelivered messages, oldest first.
     *
     * @return list<OutboxMessagePublished>
     */
    public function claimUnpublished(int $limit): array;

    /** Stamp the row delivered (the relay published it successfully). */
    public function markPublished(string $outboxId): void;

    /** Bump the row's attempt count; it stays undelivered for the next run. */
    public function markFailed(string $outboxId): void;

    /**
     * Hard-delete DELIVERED rows committed before `$cutoff` (`published_at` set
     * and `< $cutoff`) — the relay never re-claims a published row, so this only
     * reclaims space; undelivered rows are never touched. The retention prune.
     *
     * @return int rows removed
     */
    public function deletePublishedOlderThan(DateTimeImmutable $cutoff): int;

    /**
     * Count DELIVERED rows committed before `$cutoff` WITHOUT deleting them — the
     * read-only preview backing `coolms:outbox:prune --dry-run`.
     */
    public function countPublishedOlderThan(DateTimeImmutable $cutoff): int;
}
