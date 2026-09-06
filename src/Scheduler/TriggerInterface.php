<?php

declare(strict_types=1);
namespace CoolMS\Core\Scheduler;

use CoolMS\Core\Scheduler\TriggerKind;
use CoolMS\Core\Scheduler\InvalidTriggerSpecException;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Strategy for resolving the next firing of a Schedule.
 *
 * Each implementation handles one {@see TriggerKind}. The
 * `SchedulerService` in the consuming application consumes
 * implementations via the `coolms.scheduler.trigger` autoconfigured tag
 * (per ADR-118: the registry is leaf-light so eager
 * `TaggedIteratorArgument` injection is fine -- the same triage that
 * applies to the Calendar holiday rule materializers).
 *
 * **Why wrap dragonmantank/cron-expression behind this seam.** The
 * library is excellent but its API + exception types are vendor-shaped;
 * keeping our Domain seam thin means a future swap (or addition of a
 * third trigger kind like "delay-from-event") does not ripple beyond
 * the `Trigger` in the consuming application namespace.
 */
interface TriggerInterface
{
    public function supports(TriggerKind $kind): bool;

    /**
     * Compute the next firing strictly after `$from`.
     *
     * Returns `null` when the spec has no future occurrence (e.g. an
     * RRULE whose COUNT is exhausted before `$from`, or whose UNTIL is
     * past). Returns a `DateTimeImmutable` in `$tz` otherwise.
     *
     * @throws InvalidTriggerSpecException when `$spec` is unparseable / invalid for this kind
     */
    public function nextRunAfter(string $spec, DateTimeZone $tz, DateTimeImmutable $from): ?DateTimeImmutable;

    /**
     * Compute the next `$count` firings strictly after `$from` (a
     * preview / "upcoming runs" read).
     *
     * **NOT equivalent to looping {@see nextRunAfter}.** Each kind owns
     * its own multi-occurrence semantics, and a naive loop is WRONG for
     * COUNT-bounded RRULEs: `RRuleTrigger` anchors DTSTART at `$from`, so
     * feeding each result back as the next `$from` re-anchors the
     * sequence and COUNT never exhausts. So:
     *  - RRULE expands from a SINGLE DTSTART (`$from`) and respects
     *    COUNT / UNTIL — the list is shorter than `$count` once the
     *    sequence ends.
     *  - Cron is memoryless, so it loops `nextRunAfter` (and effectively
     *    never runs dry).
     *  - One-shot ("at") yields AT MOST ONE element regardless of
     *    `$count`.
     *
     * Returns an ascending `list<DateTimeImmutable>` in `$tz` of length
     * `0..$count`. A `$count` < 1 yields `[]`.
     *
     * @throws InvalidTriggerSpecException when `$spec` is unparseable / invalid for this kind
     *
     * @return list<DateTimeImmutable>
     */
    public function nextRunsAfter(string $spec, DateTimeZone $tz, DateTimeImmutable $from, int $count): array;

    /**
     * Validate the spec at admin-write time. Throws on invalid input.
     *
     * Separate from {@see nextRunAfter} so the API surface can return a
     * clean 422 from a Processor without computing an actual next run.
     *
     * @throws InvalidTriggerSpecException
     */
    public function validateSpec(string $spec): void;
}
