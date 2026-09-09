<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\TimerDefinition;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Intermediate Timer Catch Event -- a flow-on-the-wire pause.
 * The {@see $timer} VO carries exactly one of `duration | date | cycle`
 * (validator-enforced via `WF.TIMER_MULTIPLE_KINDS`).
 *
 * Source JSON uses `type: 'intermediateCatchEvent'` with
 * `subtype: 'timer'` (design section 2.3); the parser narrows the subtype to
 * this concrete AST class so the validator and engine dispatch via
 * type without a runtime branch.
 */
final class IntermediateTimerEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateTimerEvent;
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
        public readonly TimerDefinition $timer,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitIntermediateTimerEvent($this);
    }
}
