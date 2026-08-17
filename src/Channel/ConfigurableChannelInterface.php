<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * An {@see OutboundChannelInterface} that needs per-section settings before it
 * can deliver.
 *
 * ## Why a SECOND interface instead of a method on the first
 *
 * Most of the contract is "how do I send"; this is "what do I need first", and
 * plenty of channels need nothing — `rss` derives everything from the section
 * it is announcing. Folding `configFields()` into `OutboundChannelInterface`
 * would make every implementation return an empty array to say "not
 * applicable", and would break every channel written against the current
 * interface, including ones outside this repository.
 *
 * ## What implementing it buys
 *
 * The admin's section-properties dialog renders the declared fields itself, so
 * a channel gets an editor without touching the front end, and the distribution
 * write knows which keys are legitimate for that channel. Before this, the
 * dialog carried ONE hard-coded "Webhook URL" input, which is why a chat
 * channel — whose `deliver()` reads `botToken` and `chatId` — could be selected but never
 * configured, and skipped silently forever.
 *
 * Fields marked {@see ChannelConfigField::$secret} are never read back to the
 * client and are resolved through the secret store, so a bot token does not end
 * up in a content node's `extras`.
 */
interface ConfigurableChannelInterface extends OutboundChannelInterface
{
    /**
     * The settings this channel reads out of `deliver()`'s `$config`.
     *
     * Order is presentation order in the admin.
     *
     * @return list<ChannelConfigField>
     */
    public function configFields(): array;
}
