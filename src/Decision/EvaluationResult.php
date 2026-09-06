<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use Symfony\Component\Uid\Uuid;

/**
 * Result returned by `DecisionEvaluator::evaluate` in the consuming application (M3.1.f).
 *
 * **Shape of `$value`** depends on the table's hit policy and the
 * number of declared output columns:
 *  - UNIQUE / FIRST / PRIORITY / ANY -- a single matched row.
 *    Single-output table => the scalar output value directly.
 *    Multi-output table  => `array<string, mixed>` keyed by output name.
 *  - COLLECT (no aggregator) -- a list of matched rows.
 *    Single-output table => `list<mixed>` of the per-row scalar values.
 *    Multi-output table  => `list<array<string, mixed>>`.
 *  - COLLECT (SUM/MIN/MAX) -- the numeric fold across matched rows of
 *    the single declared output column (DMN spec: SUM/MIN/MAX require
 *    a single numeric output).
 *  - COLLECT (COUNT) -- int, the count of matched rows.
 *
 * If NO rules matched and the table's hit policy permits empty results
 * (everything except COLLECT-COUNT which always returns 0), `$value`
 * is null. The {@see DecisionEvaluator} configures per-policy
 * empty-match handling so callers get a uniform "null = no match"
 * signal independent of hit policy semantics.
 *
 * `$matchedRules` is the audit trail -- which rules fired, in what
 * order, with what per-rule output values. M4 cockpit reads this for
 * the decision-instance detail view; the dmn:evaluate service-task
 * handler (M3.1.h) writes a digest into the engine history payload.
 *
 * `$decisionVersionId` identifies the {@see
 * `DecisionDefinitionVersion`} whose deployed body
 * actually produced this result (#1557). It is REQUIRED, not optional: every
 * real evaluation resolves a version before it can parse a body, so a result
 * that cannot name its version does not correspond to anything that ran.
 *
 * **Why it must travel on the result.** {@see DecisionEvaluator::evaluate}
 * resolves the version from `$definition->latestVersionId` AT CALL TIME.
 * Without carrying it, an audit replay months later re-resolves "latest" and
 * can silently get a DIFFERENT answer than the one that was recorded, with
 * nothing anywhere identifying the divergence -- the exact reproducibility
 * hole ADR-113 §"Reproducibility" says must not exist. Recording which
 * version ran is orthogonal to (and safe under) that ADR's still-open
 * question of WHICH version a running instance ought to select.
 */
final readonly class EvaluationResult
{
    /**
     * @param list<MatchedRule> $matchedRules      document-order list of rules that fired
     * @param Uuid              $decisionVersionId the deployed version that produced this result
     */
    public function __construct(
        public mixed $value,
        public array $matchedRules,
        public Uuid $decisionVersionId,
    ) {
    }
}
