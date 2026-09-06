<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\StartEventVariant;
use CoolMS\Core\Workflow\MessageDefinition;
use CoolMS\Core\Workflow\SignalDefinition;
use CoolMS\Core\Workflow\TimerDefinition;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Start Event. Three variants in scope at this stage: `none`,
 * `message`, `timer` (per the verification design). Validator
 * enforces the message/timer payload presence aligns with
 * {@see $variant}; constructor accepts the slice unaware.
 *
 * Per the design doc §2.3, a start event has zero incoming flows and
 * exactly one outgoing flow. The validator owns that cardinality
 * check (`WF.START_HAS_INCOMING`).
 */
final class StartEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::StartEvent;
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
        public readonly StartEventVariant $variant,
        public readonly ?MessageDefinition $message = null,
        public readonly ?TimerDefinition $timer = null,
        public readonly ?SignalDefinition $signal = null,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitStartEvent($this);
    }
}
