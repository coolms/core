<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\DecisionDefinitionAst;

/**
 * Contract for a single deploy-time DMN-validation rule.
 *
 * Mirrors {@see \CoolMS\Core\Workflow\ValidationRuleInterface}
 * for the BPMN-Lite validator. The duplicated shape will
 * consolidate once a third deployable-definition module lands (a form definition
 * FormDefinition) and we extract a generic Definition-tier rule
 * interface; doing the consolidation now would be premature.
 *
 * Implementations are wired into the
 * `ValidationRuleRegistry` in the consuming application
 * via the `coolms.decision.validation_rule` DI tag and orchestrated
 * by `DecisionValidator` in the consuming application.
 *
 * Each rule:
 *  - Returns its sort key via {@see priority}. Lower runs first.
 *    Priority bands (matching the BPMN-Lite validator):
 *      - 100..199 Pass 1 -- Structural identity (duplicate ids).
 *      - 200..299 Pass 2 -- Reference integrity (output-name rules).
 *      - 300..399 Pass 3 -- Semantic / EL-syntax (cell expressions).
 *  - Inspects the AST inside {@see check} and records findings via
 *    `$ctx->violations->add(new Violation(...))`. Never throws on
 *    AST shape -- the orchestrator does the collect-all walk; rules
 *    MUST collect-all rather than short-circuit so the author sees
 *    every problem per round trip.
 *
 * Rules are stateless across runs; the {@see ValidationContext}
 * carries every per-run datum.
 */
interface ValidationRuleInterface
{
    public function priority(): int;

    public function check(DecisionDefinitionAst $ast, ValidationContext $ctx): void;
}
