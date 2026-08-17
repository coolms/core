<?php

declare(strict_types=1);

namespace CoolMS\Core\Webhook;

use RuntimeException;

/**
 * A signed inbound webhook failed HMAC verification — a missing or a wrong
 * signature. The receiving controller maps it to HTTP 401, opaquely (a missing
 * and a wrong signature are reported the same way so an unauthenticated caller
 * learns nothing). Shared across the platform's secret-store-keyed inbound
 * webhooks (the email and telephony receivers).
 */
final class WebhookSignatureException extends RuntimeException
{
    public static function missingSignature(): self
    {
        return new self('Missing webhook signature.');
    }

    public static function invalidSignature(): self
    {
        return new self('Invalid webhook signature.');
    }
}
