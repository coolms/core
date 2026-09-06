<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementInterface;
use CoolMS\Core\Workflow\StartEventAst;
use CoolMS\Core\Workflow\BoundaryEventAst;
use CoolMS\Core\Workflow\FlowSet;
use CoolMS\Core\Workflow\SequenceFlowAst;
use CoolMS\Core\Workflow\ElementMap;
use CoolMS\Core\Workflow\VariableMap;
use CoolMS\Core\Workflow\AstViolationException;

use function array_keys;
use function array_pop;
use function sprintf;
use function trim;

/**
 * Root AST node — the shared output shape of the BPMN-Lite JSON
 * parser and the future BPMN XML parser. Immutable.
 *
 * All cross-element references inside the tree are id-based strings
 * (design doc §2.5) — the AST graph never carries object refs across
 * the element ↔ flow boundary. Authors lift the strings into typed
 * lookups via {@see element()} / {@see flow()}.
 *
 * The root exposes a property-hook-cached flow index
 * ({@see $flowsByElement}) so callers don't repeatedly scan the flat
 * flow list. The index is built lazily on first read and cached for
 * the lifetime of the (immutable) AST instance — validator rules pay
 * the indexing cost exactly once per `validate()` call.
 *
 * Carries the parser's INFO-only `$version` (the deployer mints the
 * real monotonic version# ); ignore at parse time. The
 * sectionId / ownerId fields live on the `WorkflowDefinition` in the consuming application
 * entity, never on the AST — the soft-reference convention + the
 * AST stays a pure parsing artefact (design doc §3.5 last row).
 */
final class ProcessDefinitionAst
{
    /**
     * Lazy index: element id → {incoming flow ids, outgoing flow ids}.
     *
     * Property hook — built once on first read, then cached for the
     * lifetime of this readonly instance. Validators
     * (`FlowEndpointsExistRule`, `GatewayDegreeRule`,
     * `IncomingOutgoingConsistencyRule`, `ReachabilityRule`) read
     * from this map rather than re-scanning {@see $flows} per element.
     *
     * @var array<string, FlowSet>
     */
    public array $flowsByElement {
        get => $this->indexFlows();
    }

    /**
     * @param list<SequenceFlowAst>  $flows
     * @param list<BoundaryEventAst> $boundaryEvents
     * @param array<string, string>  $scopeParents   child element id → owning
     *                                               `subProcess` element id, from
     *                                               each entry's `"parent"` field.
     *                                               Absent = root scope. A body
     *                                               with no subprocesses passes
     *                                               `[]` and behaves exactly as it
     *                                               did before scopes existed.
     *
     * @throws AstViolationException when `$id` is blank
     */
    public function __construct(
        public readonly string $id,
        public readonly int $version,
        public readonly ?string $documentation,
        public readonly VariableMap $variables,
        public readonly ElementMap $elements,
        public readonly array $flows,
        public readonly array $boundaryEvents,
        public readonly ?SourceLocation $sourceLocation = null,
        public readonly array $scopeParents = [],
    ) {
        if ('' === trim($id)) {
            throw new AstViolationException('Process id must not be empty.', $sourceLocation);
        }
    }

    /**
     * @throws AstViolationException when `$id` does not resolve in
     *                               the element map
     */
    public function element(string $id): ElementInterface
    {
        return $this->elements->get($id)
            ?? throw new AstViolationException(sprintf('Element "%s" not found.', $id));
    }

    /**
     * The `subProcess` element owning `$elementId`, or `null` when it
     * sits at the root of the process.
     *
     * One level of lookup, not a walk: `$scopeParents` is already the
     * transitive-free child→parent edge, and nesting is resolved by
     * repeated calls rather than by flattening here (a caller asking
     * "which scope is this in" always means the IMMEDIATE one).
     */
    public function scopeOf(string $elementId): ?string
    {
        return $this->scopeParents[$elementId] ?? null;
    }

    /**
     * Element ids DIRECTLY inside `$subProcessId` (not transitively —
     * a nested subprocess appears here, its children do not).
     *
     * @return list<string>
     */
    public function childrenOf(string $subProcessId): array
    {
        $children = [];
        foreach ($this->scopeParents as $childId => $parentId) {
            if ($parentId === $subProcessId) {
                $children[] = $childId;
            }
        }

        return $children;
    }

    /**
     * The start event that a token entering `$subProcessId` is minted
     * at.
     *
     * `SubProcessScopeRule` guarantees exactly one at deploy time, so
     * a null here means the AST was hand-built or the rule regressed —
     * the caller raises rather than silently doing nothing.
     */
    public function startElementOfScope(string $subProcessId): ?StartEventAst
    {
        foreach ($this->childrenOf($subProcessId) as $childId) {
            $child = $this->elements->get($childId);
            if ($child instanceof StartEventAst) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Every element id transitively inside `$subProcessId`, including
     * the children of nested subprocesses.
     *
     * Used when an interrupting boundary kills a whole scope: killing
     * only the direct children would strand a token sitting two levels
     * down.
     *
     * @return list<string>
     */
    public function descendantsOf(string $subProcessId): array
    {
        $found = [];
        $queue = $this->childrenOf($subProcessId);

        while ([] !== $queue) {
            $id = array_pop($queue);
            if (isset($found[$id])) {
                continue;
            }
            $found[$id] = true;
            foreach ($this->childrenOf($id) as $grandChild) {
                $queue[] = $grandChild;
            }
        }

        return array_keys($found);
    }

    /**
     * @throws AstViolationException when `$id` does not resolve in
     *                               the flow list
     */
    public function flow(string $id): SequenceFlowAst
    {
        foreach ($this->flows as $f) {
            if ($f->id === $id) {
                return $f;
            }
        }

        throw new AstViolationException(sprintf('Sequence flow "%s" not found.', $id));
    }

    /**
     * Whole-tree walk. Order: enterDefinition → every element (via
     * `$element->accept($visitor)`) → every sequence flow → every
     * boundary event → leaveDefinition.
     *
     * Boundary events come after flows because the validator's G7
     * pass reads the boundary host's `incomingIds` / `outgoingIds`
     * (filled by flow visits) when checking host kind.
     */
    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->enterDefinition($this);

        foreach ($this->elements as $element) {
            $element->accept($visitor);
        }

        foreach ($this->flows as $flow) {
            $visitor->visitSequenceFlow($flow);
        }

        foreach ($this->boundaryEvents as $boundary) {
            $visitor->visitBoundaryEvent($boundary);
        }

        $visitor->leaveDefinition($this);
    }

    /**
     * Build the per-element flow adjacency index by single-pass scan
     * over {@see $flows}. Bucketing per (sourceId, targetId) is
     * O(F) where F is the flow count; the resulting map is
     * O(E) where E is element-with-edges count.
     *
     * Elements that appear in {@see $elements} but have no flow are
     * still emitted with an empty {@see FlowSet} — keeps validator
     * loops uniform.
     *
     * @return array<string, FlowSet>
     */
    private function indexFlows(): array
    {
        /** @var array<string, list<string>> $incoming */
        $incoming = [];
        /** @var array<string, list<string>> $outgoing */
        $outgoing = [];

        foreach ($this->flows as $flow) {
            $outgoing[$flow->sourceId][] = $flow->id;
            $incoming[$flow->targetId][] = $flow->id;
        }

        /** @var array<string, FlowSet> $index */
        $index = [];

        foreach ($this->elements as $element) {
            $elementId = $element->id;
            $index[$elementId] = new FlowSet(
                incomingIds: $incoming[$elementId] ?? [],
                outgoingIds: $outgoing[$elementId] ?? [],
            );
        }

        // Also expose endpoint ids that weren't in the element map
        // (e.g. dangling flow with a typo in `target`). The validator's
        // `FlowEndpointsExistRule` notices these via the element-side
        // lookup; surfacing them here too lets walkers reason about
        // every distinct id mentioned in the tree.
        foreach ($incoming as $endpointId => $flowIds) {
            if (!isset($index[$endpointId])) {
                $index[$endpointId] = new FlowSet(
                    incomingIds: $flowIds,
                    outgoingIds: $outgoing[$endpointId] ?? [],
                );
            }
        }

        foreach ($outgoing as $endpointId => $flowIds) {
            if (!isset($index[$endpointId])) {
                $index[$endpointId] = new FlowSet(
                    incomingIds: $incoming[$endpointId] ?? [],
                    outgoingIds: $flowIds,
                );
            }
        }

        return $index;
    }
}
