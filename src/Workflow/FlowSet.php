<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Per-element flow adjacency pair — the cached value type stored in
 * {@see \CoolMS\Core\Workflow\ProcessDefinitionAst::$flowsByElement}
 * (element id → FlowSet).
 *
 * Carries the list of sequence-flow ids entering an element
 * ({@see $incomingIds}) and the list leaving it ({@see $outgoingIds}).
 * Both lists hold flow ids (strings), NOT flow objects — consistent
 * with the rest of the AST's id-string cross-reference convention
 * (design doc §2.5).
 *
 * Built lazily by {@see \CoolMS\Core\Workflow\ProcessDefinitionAst::indexFlows()}
 * on first access; cached for the lifetime of the (immutable) AST.
 * Validators (`FlowEndpointsExistRule`, `GatewayDegreeRule`,
 * `IncomingOutgoingConsistencyRule`, `ReachabilityRule`) read from
 * this index rather than re-scanning the flow list per element.
 */
final readonly class FlowSet
{
    /**
     * @param list<string> $incomingIds sequence-flow ids whose
     *                                  `targetId` is this element
     * @param list<string> $outgoingIds sequence-flow ids whose
     *                                  `sourceId` is this element
     */
    public function __construct(
        public array $incomingIds,
        public array $outgoingIds,
    ) {
    }
}
