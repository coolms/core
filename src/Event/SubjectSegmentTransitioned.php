<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use DateTimeImmutable;

/**
 * A CDP subject just ENTERED or EXITED an audience segment — the Track E
 * **Phase 5 (activation)** substrate. Dispatched once per membership delta by
 * the analytics module's segment-membership recomputer (the
 * sole writer of `Subject.segments`), AFTER the change is committed.
 *
 * **The Open/Closed activation seam.** Activation is "many consumers react to a
 * membership change", so it is modelled as one Core (L0) domain event that any
 * module may listen to independently — exactly like {@see StartWorkflowRequested}
 * (the cross-module workflow-start seam). The recomputer stays closed to
 * modification while Phase 5 grows: this ships with a single listener
 * (the analytics module's transition recorder,
 * aggregate telemetry), and later slices add a journey-start listener (Workflow),
 * a real-time push, and an external-CDP fan-out — with zero
 * recomputer changes.
 *
 * Carries the FULL subject identity ({@see $subjectKey} + {@see $userRef}) so a
 * consumer can target the specific subject (e.g. start a journey for that user).
 * The aggregate analytics event the default listener records deliberately drops
 * that identity (see its docblock) — the identity lives HERE, on the event, not
 * in the anonymised telemetry stream.
 */
final readonly class SubjectSegmentTransitioned
{
    /**
     * @param string  $subjectKey the CDP subject key — `known:<userId>` (durable) or `anon:<visitorRef>` (ephemeral)
     * @param ?string $userRef    the platform user id (rfc4122) for a KNOWN subject; null for an anonymous one
     * @param string  $segmentKey the segment whose membership changed
     */
    public function __construct(
        public string $subjectKey,
        public ?string $userRef,
        public string $segmentKey,
        public SegmentTransitionDirection $direction,
        public DateTimeImmutable $occurredAt,
    ) {
    }

    public function entered(): bool
    {
        return SegmentTransitionDirection::Entered === $this->direction;
    }
}
