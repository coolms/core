<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

use function trim;

/**
 * A business-critical event queued for reliable, at-least-once delivery via the
 * transactional outbox. The producer appends one of these in the
 * SAME DB transaction as its domain change through {@see OutboxAppenderInterface},
 * so *event-exists ⇔ state-changed* is atomic — no dual-write loss/duplication.
 *
 * This is the OPT-IN reliable path: fire-and-forget signals (the analytics stream
 * for one) stay best-effort and do NOT pay the outbox cost. Use the outbox only
 * for events whose loss or duplication has business consequences.
 *
 * `payload` is the serialisable event body; `messageId` is the producer's stable
 * id for the event (the consumer's idempotency key — a later F7 slice) and falls
 * back to a generated id at append time when omitted; `occurredAt` is when the
 * event semantically happened (defaults to the append time).
 */
final readonly class OutboxMessage
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $type,
        public array $payload = [],
        public ?string $messageId = null,
        public ?DateTimeImmutable $occurredAt = null,
    ) {
        if ('' === trim($this->type)) {
            throw new InvalidOutboxMessageException('Outbox message type must not be empty.');
        }
    }
}
