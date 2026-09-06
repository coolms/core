<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\ConditionExpression;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Intermediate Conditional Catch Event (Workflow-engine-completeness
 * arc). A flow-on-the-wire pause that resumes when its EL {@see $condition}
 * becomes TRUE against the process variables — the variable-driven counterpart
 * of the timer (clock-driven), message (correlation-driven), and signal
 * (broadcast-driven) catch events.
 *
 * Source JSON uses `type: 'intermediateCatchEvent'` with `subtype: 'condition'`
 * — the same dual-spelling shape as the other catch subtypes — plus a
 * `condition` EL string. The validator (`ElConditionSyntaxRule` in the consuming application)
 * flags a blank expression (`WF.CONDITION_MISSING`) and lints the EL.
 *
 * **Engine mechanics.** `IntermediateConditionalEventStrategy` in the consuming application
 * evaluates the condition ON ENTRY: true → fire immediately (advance); false →
 * park under the `'condition:<elementId>'` discriminator. There is no external
 * resumer (no timer transport, no correlator, no broadcaster) — instead
 * `TokenAdvancer` in the consuming application RE-EVALUATES every parked
 * conditional token at the natural quiescence point of its advance loop (when
 * no `Active` token remains but the instance is still running). That point is
 * exactly after a variable-mutating step settles — a service task writing its
 * output, a user-task completion, or a message/signal payload merge — so the
 * conditional fires without any per-variable-write cost or re-entrancy.
 */
final class IntermediateConditionalEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateConditionalEvent;
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
        public readonly ConditionExpression $condition,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitIntermediateConditionalEvent($this);
    }
}
