<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\LoopCharacteristics;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite **embedded subprocess** -- an activity that contains its
 * own start event, elements and end event(s), all running in the same
 * process instance.
 *
 * **This node carries no children.** Per the design's section 2.5 rule that
 * "cross-element references are id strings (not nested children)", the
 * elements inside a subprocess stay in the SAME flat `elements[]` list
 * and declare `"parent": "<subProcessId>"`. The root AST indexes that
 * into {@see \CoolMS\Core\Workflow\ProcessDefinitionAst::childrenOf}
 * / {@see \CoolMS\Core\Workflow\ProcessDefinitionAst::scopeOf}.
 *
 * **Why flat rather than nested**: `ProcessDefinitionAst::element()` is
 * an O(1) lookup used by every validator rule, the boundary-event
 * resolver, the compensation scan and the conditional re-evaluator. A
 * recursive element tree would have forced all of them to walk. Flat +
 * a scope index leaves every one of those paths working untouched, and
 * a body with no `parent` anywhere behaves exactly as it did before
 * subprocesses existed.
 *
 * **Runtime shape**: entering the subprocess parks the arriving token
 * on `subprocess:<id>` and mints a child token at the scope's start
 * event; when the scope goes quiet (no Active or Waiting token left
 * inside it) the parent token resumes through the subprocess's single
 * outgoing flow. Boundary events attach to the subprocess exactly as
 * they do to a task -- which is what finally gives escalation a scope
 * to escalate OUT of.
 *
 * `triggeredByEvent` (BPMN event subprocess) is **not** modelled here;
 * an event subprocess has no incoming flow and is started by an event,
 * which is a different runtime shape.
 */
final class SubProcessAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::SubProcess;
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
        /** Multi-instance marker: run the whole scope once per item. */
        public readonly ?LoopCharacteristics $loop = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitSubProcess($this);
    }
}
