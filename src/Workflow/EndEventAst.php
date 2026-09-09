<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\EndEventVariant;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite End Event. M2 ships only the `none` variant; terminate /
 * error / escalation / cancel / signal end events are rejected by the
 * validator's `UnsupportedConstructRule`. The variant axis is kept on
 * the AST anyway so a future M3+ promotion can land without a node
 * shape rewrite.
 *
 * Per the design doc section 2.3, an end event has zero outgoing flows; the
 * validator owns the `WF.END_HAS_OUTGOING` check.
 */
final class EndEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::EndEvent;
    }

    /**
     * @param list<string> $incomingIds
     * @param list<string> $outgoingIds
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly array $incomingIds,
        public readonly array $outgoingIds,
        public readonly EndEventVariant $variant,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitEndEvent($this);
    }
}
