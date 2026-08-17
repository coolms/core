<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * F3 — resolves an {@see OutboundChannelInterface} by its `channelId()`.
 *
 * The single lookup surface the `channel:publish` service task (and, later, the
 * distribution workflow's per-section fan-out) uses. Consumers type-hint this
 * interface so the implementation can be decorated (auditing, feature-gating)
 * without consumer changes.
 */
interface OutboundChannelRegistryInterface
{
    /** True when a channel with this id is registered. */
    public function has(string $channelId): bool;

    /** The channel for this id, or null when none is registered. */
    public function get(string $channelId): ?OutboundChannelInterface;

    /**
     * Every ENABLED channel id (for admin pickers / diagnostics).
     *
     * Excludes channels switched off in `coolms_core.outbound_channels`
     * — so a disabled channel is invisible to the picker AND rejected
     * by the write that validates against this list, which is the point: one
     * gate, not a filtered dropdown with an open API behind it.
     *
     * @return list<string>
     */
    public function channelIds(): array;

    /**
     * Channel ids that ARE installed but are switched off.
     *
     * Exists purely so a rejection can say "registered but disabled" instead of
     * "unknown channel" — the two mean very different things to whoever has to
     * fix it: one is a typo, the other is a config decision.
     *
     * @return list<string>
     */
    public function disabledChannelIds(): array;
}
