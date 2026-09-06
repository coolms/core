<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

/**
 * DMN 1.3 hit policies (M3.1.c). The five policies the roadmap pins
 * as M3.1's evaluator surface. Out-of-spec policies (OUTPUT ORDER,
 * RULE ORDER) trip {@see \CoolMS\Core\Decision\DmnParseException}
 * with code `DMN.UNKNOWN_HIT_POLICY` at parse time.
 *
 * String values match the OMG DMN 1.3 spec letter-case for direct
 * comparison against the `<decisionTable hitPolicy="...">` attribute.
 *
 * **Semantics summary (full specs in the M3.1.f evaluator):**
 *  - `Unique`   — exactly one rule must match; multiple matches = error.
 *  - `First`    — first matching rule (in document order) wins.
 *  - `Priority` — the output entry's priority list (declared in the
 *                 `<output>` element) decides; highest-priority winning
 *                 entry across all matches selects the output row.
 *  - `Any`      — multiple rules MAY match but all their outputs MUST
 *                 be identical; otherwise = error.
 *  - `Collect`  — every matching rule contributes; the optional
 *                 {@see CollectAggregator} folds the list into a scalar
 *                 (SUM / MIN / MAX / COUNT) or returns the raw list.
 */
enum HitPolicy: string
{
    case Unique = 'UNIQUE';
    case First = 'FIRST';
    case Priority = 'PRIORITY';
    case Any = 'ANY';
    case Collect = 'COLLECT';
}
