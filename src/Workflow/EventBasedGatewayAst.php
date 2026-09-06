<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Event-Based Gateway (Workflow-engine-completeness arc). A
 * DIVERGING-only race gate: the token forks one branch per outgoing flow,
 * each targeting an intermediate CATCH event (timer / message / signal),
 * and whichever catch fires FIRST wins its branch while the losing sibling
 * branches are cancelled.
 *
 * Source JSON uses `type: 'eventBasedGateway'`. Unlike the parallel /
 * inclusive gateways it declares NO direction — an event gateway only ever
 * diverges (1 incoming, ≥2 outgoing, each to a catch event). The validator
 * enforces that shape (`GatewayDegreeRule` in the consuming application)
 * and that every target is a catch event
 * (`EventGatewayTargetsRule` in the consuming application).
 *
 * **Engine mechanics.** `EventBasedGatewayStrategy` in the consuming application
 * returns a plain FORK (identical spawn to the parallel gateway) — the
 * mutual-exclusion is entirely on the resume side: when one branch's catch
 * event resumes (`TokenAdvancer::resumeToken` in the consuming application),
 * the advancer sees the resumed token's parent element is an event gateway
 * and kills the still-parked sibling branches.
 */
final class EventBasedGatewayAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::EventBasedGateway;
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
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitEventBasedGateway($this);
    }
}
