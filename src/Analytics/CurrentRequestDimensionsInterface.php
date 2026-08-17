<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * The request-edge **derive-and-drop** seam:
 * resolves the CURRENT request's raw user-agent + referrer + query into
 * LOW-cardinality *derived* dimensions (device / os / browser family, referrer
 * type, geo country, `utm_*` campaign tags) that producers merge onto an
 * {@see AnalyticsEvent}. The raw user-agent + `Referer` + IP are read here and
 * DROPPED — only the coarse, non-identifying families ever reach the event store
 * ("~90% of the marketing value with no PII at rest").
 *
 * **Geo** (country, from a CDN edge header) and **UTM** (author-authored campaign
 * labels — high-value, not PII) are included only when present, so producers off
 * a geo proxy / non-campaign visits carry neither. Returns an empty array off the
 * request edge (CLI / worker), so producers degrade cleanly.
 *
 * Lives in Core (L0) so producers at any module level can enrich.
 */
interface CurrentRequestDimensionsInterface
{
    /**
     * @return array<string, scalar> low-cardinality derived dimensions for the
     *                               current request (empty off the request edge)
     */
    public function current(): array;
}
