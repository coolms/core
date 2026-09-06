<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\DecisionDefinitionAst;
use CoolMS\Core\Definition\Violations;

/**
 * Shared scratch space for a single Decision-validation run (M3.1.d).
 *
 * Constructed once per
 * `DecisionValidator::validate` in the consuming application
 * call via the {@see for} static factory and threaded through every
 * {@see ValidationRuleInterface::check} invocation. Mirrors the M2.c
 * {@see \CoolMS\Core\Workflow\ValidationContext} pattern --
 * the duplicated shape will consolidate into a generic Definition-
 * tier context once a third concrete (M3.3 FormDefinition) lands and
 * the abstraction earns its keep.
 *
 * Pre-computes id sets the Pass 1 duplicate-id rules would otherwise
 * rebuild from scratch. The class is `readonly` (its slot bindings
 * never change after construction), yet {@see $violations} is
 * intentionally a mutable {@see Violations} instance -- the
 * orchestrator + rules append to it throughout the run. The readonly
 * hold is on the *reference*, not the collector's internals.
 */
final readonly class ValidationContext
{
    /**
     * @param array<string, int> $inputIdCounts  id => occurrence count across <input> clauses
     * @param array<string, int> $outputIdCounts id => occurrence count across <output> clauses
     * @param array<string, int> $ruleIdCounts   id => occurrence count across <rule> rows
     */
    public function __construct(
        public DecisionDefinitionAst $ast,
        public Violations $violations,
        public array $inputIdCounts,
        public array $outputIdCounts,
        public array $ruleIdCounts,
    ) {
    }

    /**
     * Build a fresh context for the given AST with empty
     * {@see Violations} and pre-computed id-occurrence counts. The
     * `*IdCounts` shape (id => int) gives Pass 1 rules a direct
     * "occurrence >= 2 means duplicate" check without re-scanning
     * the lists.
     */
    public static function for(DecisionDefinitionAst $ast): self
    {
        $inputIdCounts = [];
        foreach ($ast->decisionTable->inputs as $input) {
            $inputIdCounts[$input->id] = ($inputIdCounts[$input->id] ?? 0) + 1;
        }

        $outputIdCounts = [];
        foreach ($ast->decisionTable->outputs as $output) {
            $outputIdCounts[$output->id] = ($outputIdCounts[$output->id] ?? 0) + 1;
        }

        $ruleIdCounts = [];
        foreach ($ast->decisionTable->rules as $rule) {
            $ruleIdCounts[$rule->id] = ($ruleIdCounts[$rule->id] ?? 0) + 1;
        }

        return new self(
            ast: $ast,
            violations: new Violations(),
            inputIdCounts: $inputIdCounts,
            outputIdCounts: $outputIdCounts,
            ruleIdCounts: $ruleIdCounts,
        );
    }
}
