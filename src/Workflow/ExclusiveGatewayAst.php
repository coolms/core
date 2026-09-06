<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Exclusive (XOR) Gateway. Direction (diverging / converging
 * / merge) is derived by the parser/validator from `in`/`out`
 * cardinality — NOT author-declared (design §2.3). The validator owns
 * the degree check (`WF.GATEWAY_DEGREE`).
 *
 * {@see $defaultFlowId} carries the optional fallback flow id when no
 * outgoing condition evaluates true at runtime. The validator confirms
 * the id is one of {@see $outgoingIds} AND that the referenced flow
 * has no `condition` (`WF.XOR_DEFAULT_INVALID`). Missing-default is a
 * warning, not an error (per locked decision: XOR no-default-fallback
 * is `WARNING` severity, deploy proceeds).
 */
final class ExclusiveGatewayAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::ExclusiveGateway;
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
        public readonly ?string $defaultFlowId = null,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitExclusiveGateway($this);
    }
}
