<?php

declare(strict_types=1);

namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;

/**
 * BPMN-Lite Intermediate Message Catch Event -- a flow-on-the-wire
 * pause that resumes when a correlated message lands. {@see $message}
 * carries the name + correlation key; the validator checks both fields
 * are present (`WF.MESSAGE_MISSING_NAME` / `WF.MESSAGE_MISSING_CORRELATION`)
 * and that the correlation key references a declared process variable
 * (`WF.MESSAGE_CORRELATION_UNDECLARED`).
 *
 * Source JSON uses `type: 'intermediateCatchEvent'` with
 * `subtype: 'message'` (design section 2.3). The parser distinguishes
 * `boundaryEvent`s by the presence of `attachedTo` -- without it, the
 * event becomes this on-the-wire intermediate catch.
 */
final class IntermediateMessageEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateMessageEvent;
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
        public readonly MessageDefinition $message,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitIntermediateMessageEvent($this);
    }
}
