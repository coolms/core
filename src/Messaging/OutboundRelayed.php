<?php

declare(strict_types=1);

namespace CoolMS\Core\Messaging;

use DateTimeImmutable;

/**
 * The event the platform dispatches for each committed outbound message it
 * relays -- the other half of {@see RelayedOutboundInterface}. A consumer
 * reacts with `#[AsEventListener(OutboundRelayed::class)]` and filters by
 * `$type`, naming nothing of the machinery that carried the message.
 *
 * `relayId` is the delivery's stable handle; `messageId` is the producer's own
 * id, which is what a consumer's idempotency check uses
 * ({@see ProcessedMessageStoreInterface}). In one process this dispatches
 * in-process; once a module is extracted the publisher behind the relay swaps
 * to a broker and consumers are unchanged.
 */
final readonly class OutboundRelayed
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $relayId,
        public string $type,
        public array $payload,
        public ?string $messageId,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
