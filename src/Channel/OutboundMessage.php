<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * F3 — the normalised payload an {@see OutboundChannelInterface} delivers.
 *
 * Transport-agnostic: a single message is handed to any channel (RSS,
 * chat, email digest, …) and each adapter performs its own format
 * adaptation (a chat provider's per-message character limit, an Atom entry, a digest
 * row, …). The producer composes ONE message; the fan-out is the distribution
 * workflow's job via the {@see OutboundChannelRegistryInterface}.
 *
 * Kept deliberately small — a headline + body + canonical link cover the M6
 * "article published, announce it" case. Anything channel-specific (attachment
 * refs, a target chat/thread id, tags) rides in `$attributes` so the VO does
 * not grow a field per channel.
 */
final readonly class OutboundMessage
{
    /**
     * @param string               $subject    short headline / title
     * @param string               $body       the message body (plain text; a channel MAY re-format it)
     * @param string|null          $url        canonical public link to the item being announced
     * @param array<string, mixed> $attributes channel-specific extras (attachment refs, target id, tags, …)
     */
    public function __construct(
        public string $subject,
        public string $body,
        public ?string $url = null,
        public array $attributes = [],
    ) {
    }
}
