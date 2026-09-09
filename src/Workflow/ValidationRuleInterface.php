<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\ProcessDefinitionAst;

/**
 * Contract for a single deploy-time validation rule.
 *
 * Implementations are wired into the
 * `ValidationRuleRegistry` in the consuming application
 * via the `coolms.workflow.validation_rule` DI tag and orchestrated
 * by `WorkflowDefinitionValidator` in the consuming application.
 *
 * Each rule:
 *  - Returns its sort key via {@see priority}. Lower runs first.
 *    Design doc section 5.1 reserves three bands: 100..199 Structural,
 *    200..299 Reference integrity, 300..399 Semantic.
 *  - Inspects the AST inside {@see check} and records findings via
 *    `$ctx->violations->add(new Violation(...))`. Never throws on
 *    AST shape -- the orchestrator does the collect-all walk; rules
 *    that bail mid-element MUST do so silently (e.g. a G7 rule
 *    skipping non-boundary elements).
 *
 * Rules are stateless across runs; the {@see ValidationContext}
 * carries every per-run datum.
 */
interface ValidationRuleInterface
{
    public function priority(): int;

    public function check(ProcessDefinitionAst $ast, ValidationContext $ctx): void;
}
