<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * The vendor-neutral event-sink port. Producers depend ONLY
 * on this; the analytics module's default persisting sink
 * persists to the local event store, but a later impl can fan out to an external
 * CDP (Segment / RudderStack / self-hosted) WITHOUT touching any producer.
 *
 * Lives in Core (L0) so producers at any module level can emit. Mirrors the
 * Search engine's port seam. Implementations MUST be best-effort: analytics
 * must never surface an error to a request that triggered it.
 */
interface AnalyticsEventSinkInterface
{
    public function record(AnalyticsEvent $event): void;
}
