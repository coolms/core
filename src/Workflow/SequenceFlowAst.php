<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ConditionExpression;

/**
 * Immutable AST node for a BPMN sequence flow — the directed edge
 * connecting two elements (start → task, task → gateway, etc.).
 *
 * Cross-element references are id strings (see design doc §2.5). The
 * parser normalises so $sourceId is non-null post-parse — even when
 * the source JSON omitted `source` and the link was inferred from the
 * source element's `out` array (see design doc §2.3 sequenceFlow note).
 *
 * Sequence flows are NOT visited via {@see accept()} — they are walked
 * by the root AST's traversal (`ProcessDefinitionAst::accept()`),
 * which dispatches to {@see \CoolMS\Core\Workflow\WorkflowAstVisitorInterface::visitSequenceFlow()}
 * directly. This mirrors the BPMN XML convention where `<sequenceFlow>`
 * lives flat under `<process>` rather than nested on its endpoints.
 *
 * `$isDefault` is a back-reference set by the parser when this flow's
 * id appears in some `exclusiveGateway.default` slot. Walkers may
 * reach the default flow either via {@see \CoolMS\Core\Workflow\ExclusiveGatewayAst}
 * or via this flag (design doc §3.5 row "Default flow on XOR").
 */
final readonly class SequenceFlowAst
{
    public function __construct(
        public string $id,
        public string $sourceId,
        public string $targetId,
        public ?ConditionExpression $condition = null,
        public bool $isDefault = false,
        public ?SourceLocation $sourceLocation = null,
    ) {
    }
}
