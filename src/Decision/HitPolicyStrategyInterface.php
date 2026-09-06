<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\HitPolicy;
use CoolMS\Core\Decision\DecisionTableAst;
use CoolMS\Core\Decision\DecisionEvaluationException;

/**
 * Strategy seam for the 5 DMN 1.3 hit policies the scope pins
 * (UNIQUE / FIRST / PRIORITY / ANY / COLLECT). One implementation per
 * {@see HitPolicy} case.
 *
 * Each strategy receives the document-ordered list of rules that
 * matched (with their EL-evaluated output values) and synthesises the
 * final {@see EvaluationResult::$value}. The orchestrator
 * (`DecisionEvaluator` in the consuming application) handles
 * the "match the inputs" + "evaluate the outputs" steps; strategies
 * are pure post-processors over the match set.
 *
 * **Discovery + lazy materialisation**: strategies are autoconfigured
 * via the `coolms.decision.hit_policy_strategy` tag and collected by
 * the `HitPolicyStrategyPass` in the consuming application
 * into the `HitPolicyStrategyRegistry` in the consuming application
 * keyed by their `supports()` return value. The registry holds
 * closures-of-strategies so the EL provider graph (pulled
 * in transitively by the strategies that use the expression
 * service) only resolves on first lookup, not on registry construction.
 *
 * Mirrors the `ElementStrategyInterface` shape.
 */
interface HitPolicyStrategyInterface
{
    /**
     * The hit policy this strategy implements. The compiler pass uses
     * this as the registry key; duplicate cases surface at compile
     * time, not runtime.
     */
    public function supports(): HitPolicy;

    /**
     * Reduce the match set to the final {@see EvaluationResult::$value}.
     *
     * @param list<MatchedRule> $matched document-ordered list of rules
     *                                   that matched (after input
     *                                   matching + output evaluation).
     *                                   MAY be empty.
     * @param DecisionTableAst  $table   the table being evaluated;
     *                                   strategies read `$outputs`
     *                                   (for column count + names) and
     *                                   `$aggregator` (COLLECT only)
     *
     * @throws DecisionEvaluationException for policy-specific
     *                                     violations (UNIQUE
     *                                     multiplicity, ANY conflict,
     *                                     SUM/MIN/MAX on non-numeric)
     */
    public function apply(array $matched, DecisionTableAst $table): mixed;
}
