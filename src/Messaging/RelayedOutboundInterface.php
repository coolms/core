<?php

declare(strict_types=1);

namespace CoolMS\Core\Messaging;

use DateTimeImmutable;

/**
 * A domain event that must survive its own transaction and be relayed after
 * the commit.
 *
 * A module dispatches such an event on the message bus exactly as it
 * dispatches any other, inside the unit of work that makes the change. The
 * platform's outbound middleware records it in the same unit of work, so the
 * record commits if and only if the change does, and a relay publishes it
 * afterwards as {@see OutboundRelayed}. The module names no table, no store
 * and no relay: it declares that this event is outbound, and the platform
 * carries it.
 *
 * The three answers are plain data, because they cross a process boundary:
 * the type a consumer matches on, a payload a consumer can act on without a
 * second read, and a stable id a consumer's idempotency check can use.
 */
interface RelayedOutboundInterface
{
    /** The stable name a consumer matches on, dotted and module-first: `lead.route_to_workflow`. */
    public function outboundType(): string;

    /** @return array<string, mixed> plain, JSON-serialisable data */
    public function outboundPayload(): array;

    /** The producer's stable id for the consumer's idempotency check, or null when it has none. */
    public function outboundMessageId(): ?string;

    /** When the thing happened, as against when it is relayed. */
    public function outboundOccurredAt(): ?DateTimeImmutable;
}
