<?php

declare(strict_types=1);
namespace CoolMS\Core\Scheduler;

/**
 * Identifies which Trigger implementation drives a Schedule.
 *
 *  - {@see CRON}  -- the `triggerSpec` is a standard 5-field cron
 *    expression (`minute hour day-of-month month day-of-week`); handled
 *    by `CronTrigger` in the consuming application.
 *  - {@see RRULE} -- the `triggerSpec` is a full RFC 5545 spec
 *    (`RRULE:...` plus optional `EXDATE:` / `RDATE:` lines per the
 *    cron parser); handled by
 *    `RRuleTrigger` in the consuming application.
 *  - {@see AT} -- a ONE-SHOT trigger: the `triggerSpec` is a single
 *    absolute ISO-8601 datetime (e.g. `2026-06-20T14:30:00+00:00`),
 *    handled by `AtTrigger` in the consuming application.
 *    It fires exactly once at that instant and then has no further
 *    occurrence (its `nextRunAfter` returns null once the moment is
 *    past). Where cron/RRULE are "recur relative to now", `at` targets
 *    an absolute future moment -- the primitive a scheduled
 *    publish/unpublish needs.
 *
 * The wire form is lowercase (`cron`, `rrule`, `at`) -- consistent with
 * the rest of the platform's enum DTO surface (cf. `HolidayRuleType` in the consuming application).
 */
enum TriggerKind: string
{
    case CRON = 'cron';
    case RRULE = 'rrule';
    case AT = 'at';

    public static function fromWire(string $value): self
    {
        $normalised = strtolower(trim($value));

        return self::from($normalised);
    }
}
