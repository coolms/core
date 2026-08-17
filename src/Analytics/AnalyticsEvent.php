<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use DateTimeImmutable;

use function preg_match;
use function sprintf;
use function strlen;

/**
 * The ONE generic, typed analytics event — the substrate every downstream
 * consumer reads from (reports, nightly rollups, the future CDP, personalization),
 * per the Track E design's "one generic event stream, many consumers" principle.
 *
 * **Privacy by construction.** This VO carries ONLY low-cardinality DERIVED
 * dimensions (geo country/region, device/os/browser family, referrer type, UTM)
 * plus a consent vector and anonymous references — there is **deliberately NO
 * raw-IP and NO user-agent field**. The raw IP/UA are resolved to dimensions +
 * an anonymous {@see VisitorReferenceGeneratorInterface} `visitorRef` at the
 * edge and then dropped ("derive-and-drop"), so nothing personal is ever
 * carried here or persisted.
 *
 * Flows through the vendor-neutral {@see AnalyticsEventSinkInterface} so
 * producers never bind to a storage/CDP vendor. Lives in Core (L0) so producers
 * at any module level can emit.
 */
final readonly class AnalyticsEvent
{
    public const int MAX_TYPE_LENGTH = 64;

    public const int MAX_PATH_LENGTH = 512;

    /**
     * @param string                $type       lowercase dotted event type, e.g. `pageview`, `lead.submit`, `search.zero_result`
     * @param ?string               $path       root-relative request path (null for non-page events)
     * @param array<string, scalar> $dimensions DERIVED low-cardinality dimensions only (country/device/referrer_type/utm_source…) — never raw IP/UA
     * @param ?float                $value      optional numeric value (e.g. order total, dwell ms)
     * @param list<string>          $consent    consent categories present at capture (e.g. `['necessary','analytics']`)
     * @param ?string               $visitorRef anonymous daily-rotating ref (null when analytics consent is absent)
     * @param ?string               $subjectRef known-subject ref once identity-resolved (null while anonymous)
     */
    public function __construct(
        public string $type,
        public DateTimeImmutable $occurredAt,
        public ?string $path = null,
        public array $dimensions = [],
        public ?float $value = null,
        public array $consent = [],
        public ?string $visitorRef = null,
        public ?string $subjectRef = null,
    ) {
        if (strlen($type) > self::MAX_TYPE_LENGTH
            || 1 !== preg_match('/^[a-z0-9]([a-z0-9._-]*[a-z0-9])?$/', $type)
        ) {
            throw new InvalidAnalyticsEventException(sprintf('Invalid analytics event type "%s".', $type));
        }
    }

    /**
     * Return a copy enriched with request-edge context — applied once, at
     * `record()` time, by the enriching sink decorator (so producers emit pure
     * domain events and never inject the request-edge readers). The three merge
     * rules encode the privacy intent:
     *
     *  - **visitorRef** fills only an absent one (a producer that already knows
     *    the ref wins);
     *  - **dimensions** are layered UNDER the event's own, so specific domain
     *    dimensions (`form`, `term`, …) win over the ambient derived ones
     *    (`device`, `referrer`, …) on a key collision;
     *  - **consent** fills only an EMPTY vector — a producer that declares its
     *    own legal basis (e.g. a deliberate conversion's `['necessary']`) keeps
     *    it, so the visitor's cookie consent never re-labels a transactional
     *    event; a behavioural producer emits `[]` and inherits the granted vector.
     *
     * @param array<string, scalar> $dimensions the derived request dimensions
     * @param list<string>          $consent    the granted consent categories
     */
    public function withRequestContext(?string $visitorRef, array $dimensions, array $consent): self
    {
        return new self(
            type: $this->type,
            occurredAt: $this->occurredAt,
            path: $this->path,
            dimensions: [...$dimensions, ...$this->dimensions],
            value: $this->value,
            consent: [] === $this->consent ? $consent : $this->consent,
            visitorRef: $this->visitorRef ?? $visitorRef,
            subjectRef: $this->subjectRef,
        );
    }
}
