<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use function array_map;
use function implode;
use function sprintf;

/**
 * Raised by
 * `UniqueHitPolicyStrategy` in the consuming application
 * when MORE than one rule matches a UNIQUE-policy table. UNIQUE
 * semantics (DMN 1.3 section 8.4.6) require exactly one match per
 * evaluation; multiple matches mean the table's rules overlap on
 * the supplied inputs, which is an authoring bug the validator
 * cannot prove statically (input domains vary by call site).
 *
 * Zero matches is NOT a violation -- UNIQUE just doesn't FIRE in
 * that case. The evaluator returns `value: null` for no-match.
 *
 * Carries the matched rule ids so cockpit (M4) can highlight the
 * offending rows on the table preview.
 */
final class UniqueHitPolicyViolatedException extends DecisionEvaluationException
{
    /**
     * @param list<string> $matchedRuleIds
     */
    public static function fromMatches(array $matchedRuleIds): self
    {
        return new self(sprintf(
            'UNIQUE hit policy: expected exactly one rule to match, got %d (rule ids: %s). The table\'s rules overlap on the supplied inputs.',
            count($matchedRuleIds),
            implode(', ', array_map(static fn (string $id): string => '"' . $id . '"', $matchedRuleIds)),
        ));
    }
}
