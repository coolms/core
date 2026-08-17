<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * C.7 — a small, counts-only read projection of a CDP `Subject`
 * profile, published for consumers that hold a soft `userId` and want to
 * cross-link to the analytics profile WITHOUT depending on the Analytics
 * module's `Subject` aggregate.
 *
 * Lives in Core (L0), like the analytics sink port, so a consumer at any module
 * level (here Contact, L1) can hold it without a level inversion. It deliberately
 * carries ONLY the privacy-safe, counts-only signal the Subject stores (no PII,
 * no raw events, by design): a deep-link handle (`key`), the total
 * `eventCount`, when the subject was `lastSeen`, and its `segments`.
 */
final readonly class SubjectSummary
{
    /**
     * @param string       $key        the subject's durable key (`known:<userId>`) — the deep-link handle
     * @param string       $kind       `known` | `anonymous` (always `known` when resolved by userId)
     * @param int          $eventCount total events attributed to the subject
     * @param string       $lastSeen   ISO-8601 (ATOM) timestamp the subject was last seen
     * @param list<string> $segments   segment keys the subject currently matches
     */
    public function __construct(
        public string $key,
        public string $kind,
        public int $eventCount,
        public string $lastSeen,
        public array $segments,
    ) {
    }
}
