<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * A channel that reaches ONE person: an SMS through an SMSC, a chat through a messenger bot, a messenger's business API.
 * The person-to-person counterpart of {@see OutboundChannelInterface}, which publishes to an audience.
 *
 * A platform capability: channel packages implement it, and the packages that send (an orchestrator of several
 * channels, one-time sign-in codes, notifications) depend on coolms/core alone, never on a channel package or on
 * each other. Registered
 * under {@see self::TAG}; a consumer finds the channel whose {@see channel()} it wants.
 *
 * Channel identity is open: each channel declares its own key, and nothing here lists the channels there are.
 *
 * **Error contract.** {@see send()} answers with what the channel said at once ({@see SendOutcome}). It THROWS only
 * when the channel could not be asked at all (a dead link, a timeout): the sender then tries again with the same key,
 * which the channel treats as the same message. Statuses that arrive later are published as
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
     * publishes the rest as {@see DeliveryReceiptReported}.
     */
    public function send(DirectMessage $message): SendOutcome;
}
