<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use DateTimeImmutable;

/**
 * The Core L0 event the outbox relay dispatches for each committed
 * {@see OutboxRecord} it publishes. Consumers react with
 * `#[AsEventListener(OutboxMessagePublished::class)]` and filter by `$type`.
 *
 * `outboxId` is the row id (the relay's mark-published handle + a stable delivery
 * id); `messageId` is the producer's id (the consumer's idempotency key, a later
 * F7 slice). In the monolith this dispatches in-process; once a module is
 * extracted the publisher behind the relay swaps to a broker — consumers unchanged.
 */
final readonly class OutboxMessagePublished
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $outboxId,
        public string $type,
        public array $payload,
        public ?string $messageId,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
