<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

use InvalidArgumentException;

use function sprintf;
use function trim;

/**
 * One message to one person, as a sender hands it to a {@see DirectChannelInterface}.
 *
 * `$sender` names who sends (`reach`, `identity`, `notification`); `$key` identifies this attempt within that sender
 * and is the channel's idempotency key: sending the same sender and key twice is one message, and every later status
 * comes back with both ({@see DeliveryReceipt}). `$address` is in the form the channel's
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
