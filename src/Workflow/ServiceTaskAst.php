<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\ConditionExpression;
use CoolMS\Core\Workflow\LoopCharacteristics;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Service Task. {@see $implementation} is a dotted handler
 * key (`notification.send`, `identity.assign_role`, …) resolved at
 * runtime against the future `WorkflowServiceTaskRegistry`.
 * The validator deliberately does NOT check the registry — only that
 * the key is non-blank (`WF.SERVICETASK_MISSING_IMPL`).
 *
 * Both {@see $inputs} and {@see $outputs} are keyed maps of EL
 * expressions:
 *  - inputs:  `map<string, ConditionExpression>` — keys are
 *    handler-defined parameter names; values are EL strings the
 *    engine evaluates against the process-variable scope.
 *  - outputs: `map<string, ConditionExpression>` — keys are
 *    LVALUES like `'vars.foo'` or `'task.<id>.output.code'`; values
 *    are EL expressions over the handler's return shape.
 *
 * Service tasks are the M2-mandatory boundary host for non-interrupting
 * MESSAGE catch events (G7 promotion — locked decision). The validator
 * enforces that restriction; this AST node accepts the slice unaware.
 *
 * {@see $forCompensation} marks this service task as a
 * COMPENSATION HANDLER — the undo activity a compensation boundary
 * ({@see \CoolMS\Core\Workflow\BoundarySubtype::Compensation}) points
 * at. A `forCompensation` handler lives OFF the normal sequence flow (no token
 * ever reaches it during normal execution); the engine invokes it directly,
 * in reverse completion order, when a `compensate` end-throw is reached.
 */
final class ServiceTaskAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::ServiceTask;
    }

    /**
     * @param list<string>                       $incomingIds
     * @param list<string>                       $outgoingIds
     * @param array<string, ConditionExpression> $inputs
     * @param array<string, ConditionExpression> $outputs         keys are lvalues like
     *                                                            'vars.foo' or
     *                                                            'task.<id>.output.code'
     * @param bool                               $forCompensation true when this service task is a
     *                                                            compensation (undo) handler invoked
     *                                                            only during compensation, never on
     *                                                            the normal sequence flow
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly array $incomingIds,
        public readonly array $outgoingIds,
        public readonly string $implementation,
        public readonly array $inputs = [],
        public readonly array $outputs = [],
        public readonly ?SourceLocation $sourceLocation = null,
        public readonly bool $forCompensation = false,
        /** Multi-instance marker: run this task once per collection item. */
        public readonly ?LoopCharacteristics $loop = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitServiceTask($this);
    }
}
