<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\GatewayDirection;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Parallel (AND) Gateway. Unlike XOR, the direction is
 * EXPLICIT -- author-declared per the design section 2.3 -- so cardinality
 * mismatches surface as deterministic validator errors rather than
 * silent semantic drift. The validator's `GatewayDegreeRule` rejects:
 *  - Diverging: must have `in == 1 && out >= 2`
 *  - Converging: must have `in >= 2 && out == 1`.
 *
 * A parallel gateway is one of the G8 host-kind workarounds -- a
 * boundary timer MAY attach to a `parallelGateway` per
 * verification-design section 3.3 (XOR gateways still reject boundary timers).
 */
final class ParallelGatewayAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::ParallelGateway;
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
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitParallelGateway($this);
    }
}
