<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\ConditionExpression;
use CoolMS\Core\Workflow\LoopCharacteristics;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite User Task. Form-bound; candidate users/groups carried as
 * EL holders so the engine resolves them at TaskInstance-creation
 * time (point-in-time snapshot).
 *
 * Boundary events on a user task are M2-restricted to interrupting
 * timers only (per the verification design). Validator
 * enforces; constructor accepts the slice unaware. Boundary events
 * are stored on the root AST, not nested here — UserTaskAst exposes
 * convenience getters via the root's index if/when needed.
 *
 * **Output mapping ({@see $outputs}).** The BPMN ioMapping OUTPUT half
 * for a user task: a keyed map of `lvalue => EL expression`, structurally
 * identical to {@see ServiceTaskAst::$outputs}.
 * When a task completes, the Inbox completion seam
 * (`TaskCompleteService` in the consuming application) EL-evaluates
 * each expression against the submitted form + current process variables
 * and PROJECTS the result into process variables — instead of merging
 * every raw form field verbatim. Keys are lvalues like `'vars.approved'`;
 * only the `vars.*` namespace writes today (mirroring `ServiceTaskInvoker`'s
 * limitation — a `task.*` output sink is a shared follow-up). An EMPTY map
 * means "no ioMapping declared" → the completion seam keeps the raw
 * merge, so every existing deployed definition is unaffected.
 */
final class UserTaskAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::UserTask;
    }

    /**
     * @param list<string>                       $incomingIds
     * @param list<string>                       $outgoingIds
     * @param list<ConditionExpression>          $candidateUserExpressions
     * @param list<ConditionExpression>          $candidateGroupExpressions
     * @param array<string, ConditionExpression> $outputs                   keys are lvalues like
     *                                                                      'vars.approved'; values are EL
     *                                                                      expressions over the submitted
     *                                                                      form + current variables
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly array $incomingIds,
        public readonly array $outgoingIds,
        public readonly string $formKey,
        public readonly ?ConditionExpression $assigneeExpression = null,
        public readonly array $candidateUserExpressions = [],
        public readonly array $candidateGroupExpressions = [],
        public readonly ?ConditionExpression $dueDateExpression = null,
        public readonly ?int $priority = null,
        public readonly array $outputs = [],
        public readonly ?SourceLocation $sourceLocation = null,
        /** Multi-instance marker: run this task once per collection item. */
        public readonly ?LoopCharacteristics $loop = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitUserTask($this);
    }
}
