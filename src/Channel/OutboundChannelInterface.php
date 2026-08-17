<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * F3 — a module-published outbound delivery channel (RSS, chat, email
 * digest, …). The outbound counterpart of the Workflow
 * the workflow module's service-task handler contract: a single
 * `channel:publish` service task resolves ONE of these from the
 * {@see OutboundChannelRegistryInterface} by `channelId()` and hands it a
 * normalised {@see OutboundMessage}, so the distribution workflow can fan an
 * article/announcement out to every configured channel without knowing any
 * channel's transport.
 *
 * Lives in Core (L0) so any module can BOTH publish a channel (Content ships
 * `rss`, a future Connector ships `telegram`) AND consume the registry. The
 * contract is transport-agnostic; each adapter owns its own format adaptation
 * (a chat provider's per-message character cap, an Atom entry, a digest row) and reads
 * its own config from `$config` (per-section channel settings live on the
 * section Node's extras; the caller passes the resolved map).
 *
 * Auto-tagged `coolms.outbound_channel` via interface autoconfiguration in the
 * Core Extension — a concrete channel needs no explicit DI.
 *
 * **Error contract:** a HARD failure (network error, provider rejection) THROWS
 * — the `channel:publish` handler wraps it as a
 * a service-task handler exception so the engine
 * surfaces it loudly. A SOFT no-op (this channel is not configured for the
 * target, nothing to push) returns {@see DeliveryResult::skipped()} instead of
 * throwing — the distribution workflow treats a skip as "this channel opted
 * out", not a failure.
 */
interface OutboundChannelInterface
{
    /**
     * Stable slug the registry keys on and the `channel:publish` task names.
     * Lower-snake, e.g. `rss`, `telegram`, `email_digest`. Two channels with the
     * same id are a fatal configuration error raised by the registry.
     */
    public function channelId(): string;

    /** Short human label for admin surfaces (e.g. "RSS feed"). */
    public function label(): string;

    /**
     * Deliver `$message` through this channel using `$config` (channel-specific
     * settings resolved by the caller, e.g. from the section Node extras).
     *
     * @param array<string, mixed> $config channel-specific configuration
     */
    public function deliver(OutboundMessage $message, array $config): DeliveryResult;
}
