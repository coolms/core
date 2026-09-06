<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use function array_map;
use function implode;
use function sprintf;

/**
 * Raised by
 * `AnyHitPolicyStrategy` in the consuming application
 * when an ANY-policy table sees multiple matches but their output
 * rows are NOT identical. ANY semantics (DMN 1.3 §8.4.6) allow
 * multiple rules to fire but REQUIRE every match to project the
 * same outputs -- the policy effectively says "any of these rules
 * gives you the same answer, so it doesn't matter which one we
 * report". A conflict means the author's rule logic disagrees with
 * the policy claim, which is an authoring bug.
 *
 * Single match (or no match) is never a conflict; ANY happily
 * reports either case.
 *
 * Carries the matched rule ids so cockpit can highlight the diverging
 * rows. (Future M4 ship may diff the output sets and highlight the
 * exact columns that diverge.)
 */
final class AnyHitPolicyConflictException extends DecisionEvaluationException
{
    /**
     * @param list<string> $matchedRuleIds
     */
    public static function fromMatches(array $matchedRuleIds): self
    {
        return new self(sprintf(
            'ANY hit policy: matched rules disagree on their outputs (rule ids: %s). ANY requires every matching rule to project the same output row.',
            implode(', ', array_map(static fn (string $id): string => '"' . $id . '"', $matchedRuleIds)),
        ));
    }
}
