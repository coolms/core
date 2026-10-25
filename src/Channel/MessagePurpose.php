<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * Why a direct message is sent, declared by its sender. One notion for the whole platform: consent decides by it
 * (marketing needs the person's marketing consent; the others need only that they have not opted out), and a
 * tariff module prices by it.
 *
 * A new purpose is a new case here, in a release of coolms/core, so every channel, consent rule and price list sees
 * the same set.
 */
enum MessagePurpose: string
{
    /** A one-time code the person asked for: a sign-in, a confirmation. */
    case Otp = 'otp';

    /** About something the person did or holds: an order, a booking, an account change. */
    case Transactional = 'transactional';

    /** Promotion the person agreed to receive. */
    case Marketing = 'marketing';

    /** Whether the person's marketing consent is needed, rather than only the absence of an opt-out. */
    public function needsMarketingConsent(): bool
    {
        return self::Marketing === $this;
    }
}
