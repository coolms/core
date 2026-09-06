<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\Violations;
use CoolMS\Core\Workflow\ProcessDefinitionAst;

/**
 * Shared scratch space for a single validation run.
 *
 * Constructed once per `WorkflowDefinitionValidator::validate` in the consuming application
 * call via the {@see for} static factory and threaded through every
 * {@see ValidationRuleInterface::check} invocation. Pre-computes the
 * id sets every reference-integrity rule (Pass 2 — see design doc
 * §5.1) would otherwise rebuild from scratch.
 *
 * The class is `readonly` (its slot bindings never change after
 * construction), yet {@see $violations} is intentionally a mutable
 * {@see Violations} instance -- the orchestrator + rules append to it
 * throughout the run. The readonly hold is on the *reference*, not
 * the collector's internals.
 */
final readonly class ValidationContext
{
    /**
     * @param array<string, true> $elementIdSet Set-shaped lookup of every
     *                                          element id present in the
     *                                          AST (also includes boundary
     *                                          event ids -- they share the
     *                                          global id namespace per
     *                                          design doc §2.2).
     * @param array<string, true> $flowIdSet    set-shaped lookup of every
     *                                          sequence flow id
     */
    public function __construct(
        public ProcessDefinitionAst $ast,
        public Violations $violations,
        public array $elementIdSet,
        public array $flowIdSet,
    ) {
    }

    /**
     * Build a fresh context for the given AST with empty
     * {@see Violations} and pre-computed id sets.
     */
    public static function for(ProcessDefinitionAst $ast): self
    {
        $elementIds = [];
        foreach ($ast->elements as $element) {
            $elementIds[$element->id] = true;
        }
        foreach ($ast->boundaryEvents as $boundary) {
            $elementIds[$boundary->id] = true;
        }

        $flowIds = [];
        foreach ($ast->flows as $flow) {
            $flowIds[$flow->id] = true;
        }

        return new self(
            ast: $ast,
            violations: new Violations(),
            elementIdSet: $elementIds,
            flowIdSet: $flowIds,
        );
    }
}
