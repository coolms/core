<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\ConditionExpression;
use CoolMS\Core\Workflow\LoopCharacteristics;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite **call activity** — invokes ANOTHER deployed definition as a
 * child process instance and waits for it to finish.
 *
 * **The difference from an embedded subprocess** ({@see SubProcessAst})
 * is where the work runs, and it is not cosmetic. A subprocess is a
 * scope inside the SAME instance, sharing its variables and dying with
 * it; a call activity starts a SEPARATE `ProcessInstance` with its own
 * id, its own variables, its own history and its own lifecycle in the
 * cockpit. That is why the two exchange data through explicit
 * {@see $inputs} / {@see $outputs} maps rather than sharing a variable
 * bag: nothing else crosses the boundary.
 *
 * `calledElement` is a definition KEY, resolved at RUNTIME against the
 * callee's currently-deployed version — not pinned at deploy time. So
 * redeploying the callee changes what subsequent calls run, which is
 * the behaviour an operator expects from "call the current version of
 * X", and it means a call activity can reference a definition that does
 * not exist yet (the validator warns; the engine fails loudly at the
 * call).
 *
 * Mapping shape mirrors {@see ServiceTaskAst}: keyed maps of EL
 * expressions. `inputs` are evaluated against the CALLER's variables to
 * build the callee's initial bag; `outputs` are evaluated against the
 * COMPLETED CHILD's variables and merged back into the caller.
 */
final class CallActivityAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::CallActivity;
    }

    /**
     * @param list<string>                       $incomingIds
     * @param list<string>                       $outgoingIds
     * @param array<string, ConditionExpression> $inputs      callee variable name → EL over the caller
     * @param array<string, ConditionExpression> $outputs     caller variable name → EL over the finished child
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly array $incomingIds,
        public readonly array $outgoingIds,
        public readonly string $calledElement,
        public readonly array $inputs = [],
        public readonly array $outputs = [],
        public readonly ?SourceLocation $sourceLocation = null,
        /** Multi-instance marker: call once per collection item. */
        public readonly ?LoopCharacteristics $loop = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitCallActivity($this);
    }
}
