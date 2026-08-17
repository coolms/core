<?php

declare(strict_types=1);

namespace CoolMS\Core\Inbox;

use DateTimeImmutable;

/**
 * Consumer idempotency store (Core L0) — the necessary partner to
 * the transactional outbox: it makes at-least-once delivery safe to process twice.
 *
 * **Usage pattern:** at the START of a transactional handler call
 * {@see firstSeen}; if it returns `false` the message is a replay — skip. Because
 * the dedupe row is written in the SAME transaction as the handler's work, the
 * two commit (or roll back) atomically — a handler that throws after `firstSeen`
 * rolls back the dedupe row too, so the message is retried, not silently dropped.
 *
 * `consumer` namespaces the dedupe so two independent handlers can each process
 * the same message id once.
 */
interface ProcessedMessageStoreInterface
{
    /**
     * Atomically record `(consumer, messageId)` as processed.
     *
     * @return bool `true` when this call recorded it for the FIRST time (proceed);
     *              `false` when it was already recorded (a replay — skip)
     */
    public function firstSeen(string $consumer, string $messageId): bool;

    /**
     * Value-carrying {@see firstSeen}: atomically claim `(consumer, messageId)`
     * FOR `$ref`, and on a replay hand back the ref that won the race.
     *
     * This exists for inbound-capture idempotency, where "already seen"
     * is not enough — a provider re-delivery of the same inbound email / PBX call
     * must map back to the SAME Lead rather than merely being skipped, so the
     * caller needs the prior aggregate id, not a boolean.
     *
     * Same atomicity guarantee and same reason for it as `firstSeen`: one
     * `INSERT ... ON CONFLICT DO NOTHING`, never a caught unique violation.
     *
     * ⚠ **Do not mix `firstSeen` and `firstSeenRef` within one `consumer`.** Rows
     * written by `firstSeen` carry no ref, so a later `firstSeenRef` replay against
     * such a row cannot return one and would be indistinguishable from a first
     * claim. Give each capture pipeline its own consumer namespace.
     *
     * @param string $ref opaque claimant id (an aggregate UUID, as a string)
     *
     * @return string|null `null` when THIS call claimed it (first time — proceed
     *                     and do the work); otherwise the ref recorded by whoever
     *                     claimed it first (a replay — reuse that aggregate)
     */
    public function firstSeenRef(string $consumer, string $messageId, string $ref): ?string;

    /** Read-only: has `(consumer, messageId)` already been processed? */
    public function hasProcessed(string $consumer, string $messageId): bool;

    /**
     * Read-only companion to {@see firstSeenRef}: the ref recorded for
     * `(consumer, messageId)`, or `null` if unclaimed (or claimed without a ref).
     *
     * Exists so a caller can ASK without CLAIMING. Using `firstSeenRef` on a read
     * path would insert a row on the miss branch — silently claiming the key for
     * a ref the caller has not committed to yet.
     */
    public function refFor(string $consumer, string $messageId): ?string;

    /**
     * Hard-delete dedupe rows processed before `$cutoff` — the retention prune.
     * The window MUST stay longer than the longest possible redelivery horizon
     * (relay retries / broker retention), or a late replay could be reprocessed.
     *
     * @return int rows removed
     */
    public function deleteProcessedOlderThan(DateTimeImmutable $cutoff): int;

    /**
     * Count dedupe rows processed before `$cutoff` WITHOUT deleting them — the
     * read-only preview backing `coolms:outbox:prune --dry-run`.
     */
    public function countProcessedOlderThan(DateTimeImmutable $cutoff): int;
}
