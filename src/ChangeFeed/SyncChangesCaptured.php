<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * "A flush just committed ≥1 row into the change feed".
 * Dispatched POST-COMMIT by the persistence adapter's change-capture
 * listener.
 *
 * Core L0 states only the FACT. It does not know edges exist, cannot nudge them,
 * and carries no payload -- the sync module (L2, which owns the fleet) decides what the
 * fact means. That is the whole reason this is a bare Core event rather than a
 * "nudge the edges" command: it keeps the sync topology out of Core entirely.
 *
 * BEST-EFFORT BY DESIGN — deliberately NOT on the transactional outbox. Losing
 * one costs latency, never correctness: an edge that misses a nudge converges on
 * its next pull. Routing it through the reliable outbox was tried and reverted
 * — one row per synced flush starved genuine business messages in the
 * relay's batch, which is exactly what the outbox's own "only if loss matters"
 * rule exists to prevent.
 */
final readonly class SyncChangesCaptured
{
}
