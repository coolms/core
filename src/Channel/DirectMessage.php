<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

use InvalidArgumentException;

use function sprintf;
use function trim;

/**
 * One message to one person, as a sender hands it to a {@see DirectChannelInterface}.
 *
 * `$sender` names who sends; `$key` identifies this attempt within that sender. Together they name the attempt: every
 * later status comes back with both ({@see DeliveryReceipt}), and a channel keeps them beside the provider's id to
 * match its receipts. They are **not** an idempotency key a provider honours: a sender sends an attempt once, and
 * resends only after a send that threw, which means nothing reached the provider
 * ({@see DirectChannelInterface}'s error contract). `$address` is in the form the channel's
 * {@see DirectChannelInterface::addressKind()} names.
 */
final readonly class DirectMessage
{
    public function __construct(
        public string $sender,
        public string $key,
        public string $address,
        public string $text,
        public MessagePurpose $purpose,
    ) {
        foreach (['sender' => $sender, 'key' => $key, 'address' => $address, 'text' => $text] as $part => $value) {
            if ('' === trim($value)) {
                throw new InvalidArgumentException(sprintf('A direct message needs a %s.', $part));
            }
        }
    }
}
