<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\TimerKind;

/**
 * Timer spec on a timer start event, intermediate timer catch event,
 * or boundary timer event. The `kind` axis (Duration / Date / Cycle)
 * is structural; the `value` is *either* an ISO-8601 literal in the
 * appropriate shape (duration `PT15M`, datetime `2026-06-01T09:00:00Z`,
 * cycle `RRULE:FREQ=DAILY...`) *or* an EL expression that resolves to
 * one at engine time.
 *
 * Disambiguation is the validator's job — see
 * `docs/investigations/m2c-design.md` §5.5(g):
 *  - `WF.TIMER_MULTIPLE_KINDS` — exactly one of duration/date/cycle
 *    per timer block in the source JSON.
 *  - `WF.TIMER_INVALID_SPEC`   — literal values type-checked by
 *    ISO-8601 regex (or `RRuleParser` for cycles); EL strings linted
 *    via `ExpressionService::lint()` against the allow-list.
 */
final readonly class TimerDefinition
{
    public function __construct(
        public TimerKind $kind,
        public string $value,
    ) {
    }
}
