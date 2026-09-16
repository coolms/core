<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use function array_map;
use function in_array;

/**
 * The tiered-consent vocabulary: the categories a visitor can grant,
 * the shared language of the consent vector that {@see CurrentConsentInterface}
 * returns and every {@see AnalyticsEvent} carries.
 *
 *  - `necessary` -- always granted (the site can't function without it); not a choice.
 *  - `analytics` -- MEASURING: the aggregate/derived analytics pipeline (page
 *    views, rollups) over a reference that rotates daily -- an audience, not a person.
 *  - `recognition` -- RECOGNISING: a durable identifier issued to this browser so
 *    that its visits can be joined into one profile over time. A different
 *    purpose from measuring, not a degree of it, and named for what it does:
 *    a banner that shows two rows reading as one thing with a checkbox has
 *    failed before it ships.
 *  - `personalization` -- segment-driven content variant selection (reuses the profile).
 *  - `marketing` -- ad targeting / retargeting / external marketing fan-out.
 *
 * !! RECOGNISING IMPLIES MEASURING. You cannot profile without counting, so
 * the upper rung entails the lower: {@see implies()} declares it, and
 * {@see closure()} applies it, so a vector that grants `recognition` grants
 * `analytics` by construction -- "profile granted, analytics declined" is not a
 * state a canonical vector can hold. The relation is declared HERE, once, in
 * the vocabulary; where collection happens the platform's consent ladder
 * applies it before deciding anything.
 *
 * Replaces the binary accept/decline posture: the public banner writes a
 * comma-list of granted slugs and each pipeline honours its own category, so a
 * visitor can accept analytics but decline recognition, personalization or
 * marketing. `RequestConsent` maps the cookie to a `list<string>` of these
 * `value`s -- the enum is the single source of the valid slugs; producers and
 * pipelines read the vector, never the enum, so nothing downstream couples to it.
 */
enum ConsentCategory: string
{
    case Necessary = 'necessary';
    case Analytics = 'analytics';
    case Recognition = 'recognition';
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

    /**
     * The categories granting this one entails. Recognising a person implies
     * measuring the audience they are part of; nothing else entails anything.
     *
     * @return list<self>
     */
    public function implies(): array
    {
        return match ($this) {
            self::Recognition => [self::Analytics],
            default => [],
        };
    }

    /**
     * The canonical vector for a set of granted slugs: known categories only,
     * in declaration order, `necessary` always present, and every entailment
     * applied -- so `['recognition']` comes back as
     * `['necessary', 'analytics', 'recognition']`. Unknown slugs are dropped.
     *
     * @param list<string> $granted
     *
     * @return list<string>
     */
    public static function closure(array $granted): array
    {
        $wanted = [self::Necessary->value];
        foreach (self::cases() as $category) {
            if (!in_array($category->value, $granted, true)) {
                continue;
            }
            $wanted[] = $category->value;
            foreach ($category->implies() as $implied) {
                $wanted[] = $implied->value;
            }
        }

        $out = [];
        foreach (self::cases() as $category) {
            if (in_array($category->value, $wanted, true)) {
                $out[] = $category->value;
            }
        }

        return $out;
    }
}
