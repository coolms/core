<?php

declare(strict_types=1);
namespace CoolMS\Core\Realtime;

use Symfony\Component\Uid\Uuid;

/**
 * A voter answers two questions for one realtime-bus channel namespace:
 *
 *   - "Do I own this channel name?" -- `supports($channel)`. Voters
 *     usually match on a literal channel name (broadcast) or a prefix
 *     ("vfs.parent.", "calendar.items."). The registry walks voters in
 *     order; the first voter that supports a channel is authoritative.
 *
 *   - "May this user subscribe?" -- `canSubscribe($userId, $channel)`.
 *     The supporting voter alone decides. Default deny is enforced by
 *     the registry when no voter supports the channel at all.
 *
 * Lives in the `Realtime` module (the transport-agnostic abstraction
 * layer) intentionally -- the contract knows nothing about the transport,
 * Mercure, or any other concrete bus. The string `$channel` is the
 * only transport-touching value, and even that is treated as opaque
 * by the registry (the owning voter parses it per its convention).
 * Swapping in Mercure would change `IssueSubscriptionTokenProcessor`
 * to a Mercure equivalent but leave every voter unchanged.
 *
 * Voters live in the module that publishes their channel namespace:
 *   - the realtime module ships voters for its broadcast/user-notification
 *     primitives
 *   - VFS owns `vfs.parent.{uuid}`
 *   - Calendar owns `calendar.items.{uuid}`
 *   - Inbox owns `inbox.{uuid}`
 *   - a grid module owns `datagrid.{alias}.list`
 *
 * voters are leaf-light and consumed by a
 * per-request API Platform processor, so the registry uses
 * `TaggedIteratorArgument` rather than the lazy-closure pattern. If a
 * future voter needs the heavy graph (NodeRepository, EntityManager)
 * the registry should escalate the escalation table in the platform docs.
 *
 * Implementations are auto-tagged via interface autoconfiguration in
 * `Realtime\Infrastructure\DependencyInjection\Extension`; concrete
 * voters do not need an explicit tag attribute.
 */
interface ChannelPolicyVoterInterface
{
    /**
     * True when this voter owns the channel namespace. The registry
     * stops at the first matching voter and asks only that voter for
     * the subscription decision.
     */
    public function supports(string $channel): bool;

    /**
     * Decide whether `$userId` may subscribe to `$channel`. Only
     * called when `supports($channel)` returned true.
     */
    public function canSubscribe(Uuid $userId, string $channel): bool;
}
