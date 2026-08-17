<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use function array_map;

/**
 * The tiered-consent vocabulary: the categories a visitor can grant,
 * the shared language of the consent vector that {@see CurrentConsentInterface}
 * returns and every {@see AnalyticsEvent} carries.
 *
 *  - `necessary` — always granted (the site can't function without it); not a choice.
 *  - `analytics` — the aggregate/derived analytics pipeline (pageviews, rollups, CDP profile).
 *  - `personalization` — segment-driven content variant selection (reuses the analytics profile).
 *  - `marketing` — ad targeting / retargeting / external marketing fan-out.
 *
 * Replaces the binary accept/decline posture: the public banner writes a
 * comma-list of granted slugs and each pipeline honours its own category, so a
 * visitor can accept analytics but decline personalization or marketing.
 * `RequestConsent` maps the cookie to a `list<string>` of these `value`s — the
 * enum is the single source of the valid slugs; producers/pipelines read the
 * vector, never the enum, so nothing downstream couples to it.
 */
enum ConsentCategory: string
{
    case Necessary = 'necessary';
    case Analytics = 'analytics';
    case Personalization = 'personalization';
    case Marketing = 'marketing';

    /**
     * All category slugs in canonical (declaration) order.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
