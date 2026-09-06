<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\CallActivityAst;
use CoolMS\Core\Workflow\EndEventAst;
use CoolMS\Core\Workflow\EventBasedGatewayAst;
use CoolMS\Core\Workflow\ExclusiveGatewayAst;
use CoolMS\Core\Workflow\InclusiveGatewayAst;
use CoolMS\Core\Workflow\IntermediateConditionalEventAst;
use CoolMS\Core\Workflow\IntermediateMessageEventAst;
use CoolMS\Core\Workflow\IntermediateMessageThrowEventAst;
use CoolMS\Core\Workflow\IntermediateSignalEventAst;
use CoolMS\Core\Workflow\IntermediateSignalThrowEventAst;
use CoolMS\Core\Workflow\IntermediateTimerEventAst;
use CoolMS\Core\Workflow\ParallelGatewayAst;
use CoolMS\Core\Workflow\ServiceTaskAst;
use CoolMS\Core\Workflow\StartEventAst;
use CoolMS\Core\Workflow\SubProcessAst;
use CoolMS\Core\Workflow\UserTaskAst;
use CoolMS\Core\Workflow\BoundaryEventAst;
use CoolMS\Core\Workflow\SequenceFlowAst;

/**
 * Whole-tree walker contract for the {@see ProcessDefinitionAst} —
 * the design doc §3.4 visitor, mirroring DTMPL's `AbstractAstNodeVisitor`
 * precedent.
 *
 * Two consumers want this seam:
 *
 *  1. `WorkflowDefinitionValidator` in the consuming application
 *     — every rule walks once via `ast->accept($ruleVisitor)`. Per-rule
 *     `match($element::class)` would have to be kept in sync as the
 *     element catalogue grows; the visitor centralises the dispatch.
 *  2. The future M2.l XML round-trip serialiser + the M3 cockpit
 *     diagram renderer — both want a generic "for each element /
 *     boundary / flow" walk that does not bake in any one consumer's
 *     element-kind ordering.
 *
 * Crucially the engine runtime (`TokenAdvancer` in the consuming application
 * — M2.f) does NOT use this visitor. It walks tokens, not the
 * whole tree, and dispatches by `ElementKind` directly. Mixing the
 * two would force every token-advance to allocate a visitor.
 *
 * Implementors typically subclass {@see AbstractWorkflowAstVisitor}
 * and override only the methods they care about.
 */
interface WorkflowAstVisitorInterface
{
    public function enterDefinition(ProcessDefinitionAst $def): void;

    public function leaveDefinition(ProcessDefinitionAst $def): void;

    public function visitStartEvent(StartEventAst $e): void;

    public function visitEndEvent(EndEventAst $e): void;

    public function visitExclusiveGateway(ExclusiveGatewayAst $g): void;

    public function visitParallelGateway(ParallelGatewayAst $g): void;

    public function visitInclusiveGateway(InclusiveGatewayAst $g): void;

    public function visitEventBasedGateway(EventBasedGatewayAst $g): void;

    public function visitUserTask(UserTaskAst $t): void;

    public function visitServiceTask(ServiceTaskAst $t): void;

    /**
     * Embedded subprocess. The visit does NOT descend — the scope's
     * children are ordinary entries in the same flat element list and
     * get their own visits from
     * {@see ProcessDefinitionAst::accept}. A rule that needs the scope
     * relationship reads `ProcessDefinitionAst::childrenOf()`.
     */
    public function visitSubProcess(SubProcessAst $s): void;

    /** Call activity -- invokes another definition as a child instance. */
    public function visitCallActivity(CallActivityAst $c): void;

    public function visitIntermediateTimerEvent(IntermediateTimerEventAst $e): void;

    public function visitIntermediateMessageEvent(IntermediateMessageEventAst $e): void;

    public function visitIntermediateSignalEvent(IntermediateSignalEventAst $e): void;

    public function visitIntermediateSignalThrowEvent(IntermediateSignalThrowEventAst $e): void;

    public function visitIntermediateMessageThrowEvent(IntermediateMessageThrowEventAst $e): void;

    public function visitIntermediateConditionalEvent(IntermediateConditionalEventAst $e): void;

    public function visitBoundaryEvent(BoundaryEventAst $b): void;

    public function visitSequenceFlow(SequenceFlowAst $f): void;
}
