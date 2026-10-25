<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * What a direct channel answered one send with, at once. Later statuses arrive as {@see DeliveryReceiptReported}.
 *
 * {@see DeliveryStatus::Failed} means the provider refused the message: the sender moves on, and the channel answered
 * rather than threw. {@see DeliveryStatus::Unknown} means it was possibly sent (the request may have been written and
 * no answer came): the sender never resends it.
 */
final readonly class SendOutcome
{
    public function __construct(
        public DeliveryStatus $status,
        /** The channel's own word: an SMPP status, an HTTP status and its description. */
        public string $detail,
        /** The provider's id for the message, when it gave one. */
        public ?string $providerId = null,
    ) {
    }
}
