<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use DateTimeImmutable;

use function in_array;
use function preg_match;
use function sprintf;
use function strlen;

/**
 * The ONE generic, typed analytics event -- the substrate every downstream
 * consumer reads from (reports, nightly rollups, the CDP, personalization),
 * per the Track E design's "one generic event stream, many consumers" principle.
 *
 * **Privacy by construction.** This VO carries ONLY low-cardinality DERIVED
 * dimensions (geo country/region, device/os/browser family, referrer type, UTM)
 * plus a consent vector and references -- there is **deliberately NO raw-IP
 * and NO user-agent field**. The raw IP/UA are resolved to dimensions + an
 * anonymous {@see VisitorReferenceGeneratorInterface} `visitorRef` at the edge
 * and then dropped ("derive-and-drop"), so nothing personal is ever carried
 * here or persisted.
 *
 * **Three references, three sources, one column each.** `visitorRef` is the
 * day's anonymous reference (computed, rotates at UTC midnight); `recognitionRef`
 * is the durable identifier a browser was ISSUED after granting `recognition`
 * (never computed; read at the edge by {@see CurrentRecognitionInterface});
 * `subjectRef` is the signed-in user. Which column holds a value says which
 * source named the visitor. Every reference is subject to the platform's
 * consent ladder at `record()` time: none is stamped without the consent that
 * covers it, whatever a producer passed.
 *
 * **The consent vector and the consent record.** `consent` is what was IN
 * FORCE when the row was written -- the vector the platform's ladder decided
 * on, record before cookie. `consentRecordId` is the decision it rests on:
 * the id of the consent record in force, or null when the decision came
 * from a cookie alone (a browser the platform could not name durably) or from
 * nothing. Rows written before consent was recorded carry a copy and no
 * reference, and the copy is the only statement of consent they will ever
 * have; rows since carry both, and the reference makes a row's consent
 * checkable against the recorded decision without repeating what it says.
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
     * @param string                $type            lowercase dotted event type, e.g. `pageview`, `lead.submit`, `search.zero_result`
     * @param ?string               $path            root-relative request path (null for non-page events)
     * @param array<string, scalar> $dimensions      DERIVED low-cardinality dimensions only (country/device/referrer_type/utm_source...) -- never raw IP/UA
     * @param ?float                $value           optional numeric value (e.g. order total, dwell ms)
     * @param list<string>          $consent         the consent categories IN FORCE at capture (e.g. `['necessary','analytics']`)
     * @param ?string               $visitorRef      anonymous daily-rotating ref (null without `analytics` consent)
     * @param ?string               $subjectRef      known-subject ref once identity-resolved (null while anonymous, and null without `analytics` consent)
     * @param ?string               $recognitionRef  the durable recognition id the browser was issued (null without `recognition` consent, or before issuance)
     * @param ?string               $consentRecordId the recorded consent decision the vector rests on (null when the decision came from a cookie alone, or from nothing)
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
        public ?string $recognitionRef = null,
        public ?string $consentRecordId = null,
    ) {
        if (strlen($type) > self::MAX_TYPE_LENGTH
            || 1 !== preg_match('/^[a-z0-9]([a-z0-9._-]*[a-z0-9])?$/', $type)
        ) {
            throw new InvalidAnalyticsEventException(sprintf('Invalid analytics event type "%s".', $type));
        }
    }

    /**
     * Return a copy enriched with request-edge context -- applied once, at
     * `record()` time, by the enriching sink decorator (so producers emit pure
     * domain events and never inject the request-edge readers). The merge
     * rules encode the privacy intent:
     *
     *  - **visitorRef** and **recognitionRef** fill only an absent one (a
     *    producer that already knows the ref wins);
     *  - **dimensions** are layered UNDER the event's own, so specific domain
     *    dimensions (`form`, `term`, ...) win over the ambient derived ones
     *    (`device`, `referrer`, ...) on a key collision;
     *  - **consent** fills only an EMPTY vector -- a producer that declares its
     *    own legal basis (e.g. a deliberate conversion's `['necessary']`) keeps
     *    it, so the visitor's cookie consent never re-labels a transactional
     *    event; a behavioural producer emits `[]` and inherits the granted vector;
     *  - **consentRecordId** fills only an absent one, for the same reason.
     *
     * @param array<string, scalar> $dimensions      the derived request dimensions
     * @param list<string>          $consent         the granted consent categories
     * @param ?string               $recognitionRef  the durable id the request carries, if any
     * @param ?string               $consentRecordId the recorded decision the granted vector rests on, if any
     */
    public function withRequestContext(
        ?string $visitorRef,
        array $dimensions,
        array $consent,
        ?string $recognitionRef = null,
        ?string $consentRecordId = null,
    ): self {
        return new self(
            type: $this->type,
            occurredAt: $this->occurredAt,
            path: $this->path,
            dimensions: [...$dimensions, ...$this->dimensions],
            value: $this->value,
            consent: [] === $this->consent ? $consent : $this->consent,
            visitorRef: $this->visitorRef ?? $visitorRef,
            subjectRef: $this->subjectRef,
            recognitionRef: $this->recognitionRef ?? $recognitionRef,
            consentRecordId: $this->consentRecordId ?? $consentRecordId,
        );
    }

    /**
     * The same event with every reference removed -- what a row may say about
     * a visitor who has not granted the consent that covers a reference:
     * everything except who. `$keep` names the references that survive.
     *
     * @param list<string> $keep property names among `visitorRef`, `subjectRef`, `recognitionRef`
     */
    public function withOnlyReferences(array $keep): self
    {
        return new self(
            type: $this->type,
            occurredAt: $this->occurredAt,
            path: $this->path,
            dimensions: $this->dimensions,
            value: $this->value,
            consent: $this->consent,
            visitorRef: in_array('visitorRef', $keep, true) ? $this->visitorRef : null,
            subjectRef: in_array('subjectRef', $keep, true) ? $this->subjectRef : null,
            recognitionRef: in_array('recognitionRef', $keep, true) ? $this->recognitionRef : null,
            consentRecordId: $this->consentRecordId,
        );
    }
}
