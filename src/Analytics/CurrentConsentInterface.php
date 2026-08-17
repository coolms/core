<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * Reads the consent categories the current visitor has granted, for the
 * request-edge enrichment of {@see AnalyticsEvent}s (the third request-edge
 * seam alongside {@see CurrentVisitorReferenceInterface} and
 * {@see CurrentRequestDimensionsInterface}).
 *
 * The vector is drawn from the visitor's consent decision — the tiered
 * {@see ConsentCategory} set, with backward-compat for the legacy
 * accept/decline cookie — and gates which downstream processing an event's data
 * is eligible for (analytics rollups, personalization, marketing). `necessary`
 * is always implicitly granted.
 *
 * Off the request edge (CLI / worker / no request) it returns `[]` — "consent
 * unknown" — so the enriching sink leaves a producer's own declared consent
 * (e.g. a deliberate conversion's `['necessary']`) untouched.
 */
interface CurrentConsentInterface
{
    /** @return list<string> granted consent categories, or [] when off the request edge */
    public function current(): array;
}
