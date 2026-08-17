<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * F3 — the typed outcome of an {@see OutboundChannelInterface::deliver()} call.
 *
 * `$delivered` is the load-bearing flag: `true` = the channel accepted/emitted
 * the message; `false` = the channel deliberately SKIPPED it (not configured
 * for this target, nothing to push, …) — a soft outcome the distribution
 * workflow can branch on, NOT a failure. A hard failure (network error, provider
 * rejection) is a thrown exception, not a `delivered: false` result.
 *
 * `$reference` carries a channel-meaningful handle for tracking — a provider
 * message id (a chat provider), the feed URL/path (RSS), a digest batch id, … — so a
 * downstream step or the cockpit can correlate the send.
 */
final readonly class DeliveryResult
{
    private function __construct(
        public string $channelId,
        public bool $delivered,
        public ?string $reference = null,
        public ?string $detail = null,
    ) {
    }

    /** The channel emitted the message. `$reference` = provider handle for tracking. */
    public static function delivered(string $channelId, ?string $reference = null, ?string $detail = null): self
    {
        return new self($channelId, true, $reference, $detail);
    }

    /**
     * The channel deliberately did nothing (not configured for this target,
     * nothing to push). A soft outcome — the caller decides if it matters.
     */
    public static function skipped(string $channelId, string $detail): self
    {
        return new self($channelId, false, null, $detail);
    }
}
