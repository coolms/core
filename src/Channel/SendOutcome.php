<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/** What a direct channel answered one send with, at once. Later statuses arrive as {@see DeliveryReceiptReported}. */
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
