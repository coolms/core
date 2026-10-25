<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * A channel that reaches ONE person: an SMS through an SMSC, a chat through a messenger bot, a business messaging API.
 * The person-to-person counterpart of {@see OutboundChannelInterface}, which publishes to an audience.
 *
 * A platform capability: channel packages implement it, and the packages that send (an orchestrator of several
 * channels, one-time sign-in codes, notifications) depend on coolms/core alone, never on a channel package or on
 * each other. Registered
 * under {@see self::TAG}; a consumer finds the channel whose {@see channel()} it wants.
 *
 * Channel identity is open: each channel declares its own key, and nothing here lists the channels there are. **One
 * channel per key**: a channel that reaches one kind of person through several provider accounts routes between them
 * behind its one key, and a second registration under a key already taken is a configuration error, never a silent
 * choice of the first.
 *
 * **Error contract: a message is never sent twice.**
 * - {@see send()} THROWS only when the request **certainly did not reach the provider**: the connection was refused,
 *   the name did not resolve, the link was down before anything was written. Nothing was sent, so the sender may try
 *   again.
 * - Once the request may have been written, the channel never throws: a timeout or a lost answer is answered with
 *   {@see SendOutcome} status {@see DeliveryStatus::Unknown} and a detail saying it was possibly sent. **A sender
 *   never resends an Unknown**: the message may have arrived, and providers take no idempotency key to stop a
 *   second copy.
 * - A provider's refusal is {@see DeliveryStatus::Failed}, answered, never thrown: the sender moves on.
 *
 * Statuses that arrive later (including for an Unknown that did arrive) are published as
 * {@see DeliveryReceiptReported}.
 */
interface DirectChannelInterface
{
    /** The service tag a direct channel is registered under. */
    public const string TAG = 'coolms.direct_channel';

    /** The channel's key: lower case, e.g. `sms`, `telegram`. */
    public function channel(): string;

    /**
     * The kind of address this channel reaches, as consent is kept and addresses are confirmed for it: `phone` for a
     * number in E.164, or a channel's own kind (a chat of one messenger bot is not a chat of another).
     */
    public function addressKind(): string;

    /**
     * The furthest this channel reports a message reaching: Accepted for a bot that sees no delivery, Delivered for SMS
     * with receipts, Read for a messenger that reports reading. A sender cannot wait for more than this.
     */
    public function reportsUpTo(): DeliveryStatus;

    /**
     * Sends one message. The outcome says how far it got at once; a channel that reports later says Accepted, and
     * publishes the rest as {@see DeliveryReceiptReported}. See the error contract above for when it may throw.
     */
    public function send(DirectMessage $message): SendOutcome;
}
