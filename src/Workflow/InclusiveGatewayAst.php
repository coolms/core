<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\GatewayDirection;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Inclusive (OR) Gateway -- the third gateway kind, completing
 * the XOR / AND / OR trio.
 *
 * Combines the two shapes of the other gateways:
 *  - Like {@see ExclusiveGatewayAst}, a diverging OR evaluates a
 *    condition on EACH outgoing flow and carries an optional
 *    {@see $defaultFlowId} fallback -- but unlike XOR (which picks
 *    exactly ONE), it activates EVERY branch whose condition is true.
 *  - Like {@see ParallelGatewayAst}, the direction is author-declared
 *    (not derived) via {@see $direction}, so cardinality mismatches
 *    surface as deterministic `WF.GATEWAY_DEGREE` validator errors:
 *      - Diverging  -> `in == 1 && out >= 2`
 *      - Converging -> `in >= 2 && out == 1`.
 *
 * The converging OR-join synchronises ONLY the branches actually
 * activated at the matching split (not every incoming flow, as the
 * parallel join does) -- see
 * `InclusiveGatewayStrategy` in the consuming application.
 */
final class InclusiveGatewayAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::InclusiveGateway;
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
        public readonly GatewayDirection $direction,
        public readonly ?string $defaultFlowId = null,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitInclusiveGateway($this);
    }
}
