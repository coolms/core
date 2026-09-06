<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\CollectAggregator;
use CoolMS\Core\Decision\HitPolicy;

/**
 * `<decisionTable>` body of a DMN decision. Immutable.
 *
 * **Aggregator constraint** (validated at parse time): `$aggregator`
 * may only be non-null when `$hitPolicy === HitPolicy::Collect`. The
 * parser raises `DMN.UNKNOWN_AGGREGATOR` if the XML declares one on a
 * non-COLLECT table; the evaluator double-checks the invariant
 * but the AST-level guard is faster + scoped to authoring errors.
 *
 * **Order matters** in two specific places:
 *  - `$inputs` document order = column order in `<rule>` entries.
 *  - `$outputs` document order = column order in `<rule>` entries.
 *  - `$rules` document order = match-priority order for
 *    {@see HitPolicy::First}, AND the column-output priority
 *    ladder for {@see HitPolicy::Priority} (the latter draws from
 *    OutputClause-level lists per OMG DMN spec; the evaluator owns
 *    the precise ladder construction).
 *
 * **Empty-table invariant**: a table with no rules trips
 * `DMN.EMPTY_DECISION_TABLE` at parse time -- DMN spec allows it but
 * a table that can never match is always an authoring bug at our
 * scale, so we fail loudly rather than silently return "no match".
 */
final readonly class DecisionTableAst
{
    /**
     * @param list<InputClauseAst>  $inputs  document order; column index for rule entries
     * @param list<OutputClauseAst> $outputs document order; column index for rule entries
     * @param list<RuleAst>         $rules   document order; first-match priority for HitPolicy::First
     */
    public function __construct(
        public HitPolicy $hitPolicy,
        public ?CollectAggregator $aggregator,
        public array $inputs,
        public array $outputs,
        public array $rules,
    ) {
    }
}
