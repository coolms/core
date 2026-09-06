<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Discriminator for the three mutually-exclusive timer spellings on a
 * {@see \CoolMS\Core\Workflow\TimerDefinition} per
 * `docs/investigations/m2c-design.md` §2.3 / §3.1.
 *
 * - `duration` — ISO-8601 duration literal or EL expression yielding one.
 * - `date`     — ISO-8601 datetime literal or EL expression.
 * - `cycle`    — RRULE literal (parsed via M1.1 `RRuleParser` at deploy
 *                time when literal) or EL expression.
 *
 * Validator rule `WF.TIMER_MULTIPLE_KINDS` enforces that exactly one
 * kind is set per timer block.
 */
enum TimerKind: string
{
    case Duration = 'duration';
    case Date = 'date';
    case Cycle = 'cycle';
}
