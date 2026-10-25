<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

use CoolMS\Core\Event\AbstractDomainEvent;

/**
 * The path by which a direct channel reports a later status: it publishes this event, and each sender subscribes and
 * takes the ones {@see isFor()} it. A channel never calls a sender: senders sit above channels, and a lower package
 * feeds a higher one by an event, never by an interface of the higher one.
 *
 * The entity id is `<sender>:<key>`: one attempt, unique across senders.
 *
 * Inbound replies (a STOP, an answer to a code) are not here yet. They will be a sibling event in this namespace,
 * published by channels that can receive them; nothing in {@see DirectChannelInterface} changes for it.
 */
final class DeliveryReceiptReported extends AbstractDomainEvent
{
    public string $eventName { get => 'channel.delivery_receipt_reported'; }

    public function __construct(
        public readonly DeliveryReceipt $receipt,
    ) {
        parent::__construct($receipt->sender . ':' . $receipt->key, $receipt->occurredAt);
    }

    /** Whether this receipt answers a message this sender sent. */
    public function isFor(string $sender): bool
    {
        return $this->receipt->sender === $sender;
    }
}
