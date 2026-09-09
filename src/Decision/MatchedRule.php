<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\RuleAst;

/**
 * One {@see RuleAst} that fired during evaluation, paired with its
 * EL-evaluated output values. Strategies consume lists of
 * these to produce the final {@see EvaluationResult}.
 *
 * `outputs` is keyed by output column NAME (falling back to the
 * column id when `OutputClauseAst::$name` is null) so callers and
 * strategies can address columns by their authored handle without
 * threading the {@see \CoolMS\Core\Decision\OutputClauseAst}
 * list through every consumer.
 *
 * `ruleIndex` is the rule's position in {@see \CoolMS\Core\Decision\DecisionTableAst::$rules}
 * (0-based, document order). Strategies that care about document order
 * (FIRST, PRIORITY tie-break, COLLECT) read this rather than reaching
 * back into the AST.
 */
final readonly class MatchedRule
{
    /**
     * @param array<string, mixed> $outputs keyed by output column name (or id when name is null)
     */
    public function __construct(
        public RuleAst $rule,
        public int $ruleIndex,
        public array $outputs,
    ) {
    }
}
