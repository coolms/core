<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

use DateTimeImmutable;
use InvalidArgumentException;

use function trim;

/**
 * A status that arrived after the send: an SMPP delivery receipt, a messenger's read report. The channel matched it to
 * the attempt (by the provider id it kept beside the sender's key) before reporting it, so it carries the sender and
 * key the message was sent with, and the sender never has to know a provider's id format.
 *
 * Apply it with {@see DeliveryStatus::then()}: a receipt never moves an attempt backwards.
 */
final readonly class DeliveryReceipt
{
    public function __construct(
        /** The reporting channel's key: informational, since a sender matches on sender and key alone. */
        public string $channel,
        public string $sender,
        public string $key,
        public DeliveryStatus $status,
        /** The channel's own word for it, e.g. "DELIVRD err:000". */
        public string $detail,
        public ?string $providerId,
        public DateTimeImmutable $occurredAt,
    ) {
        if ('' === trim($channel) || '' === trim($sender) || '' === trim($key)) {
            throw new InvalidArgumentException('A delivery receipt needs its channel, sender and key.');
        }
    }
}
