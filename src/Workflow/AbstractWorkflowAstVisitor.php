<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\CallActivityAst;
use CoolMS\Core\Workflow\EndEventAst;
use CoolMS\Core\Workflow\EventBasedGatewayAst;
use CoolMS\Core\Workflow\ExclusiveGatewayAst;
use CoolMS\Core\Workflow\InclusiveGatewayAst;
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
 * No-op base implementation of {@see WorkflowAstVisitorInterface}.
 *
 * Designed for partial subclassing: consumers (validator rules, the
 * a future XML serialiser, the cockpit diagram renderer) extend
 * this class and override only the visit methods they care about. The
 * abstract layer keeps the interface stable as new element kinds land
 * (M3 inclusiveGateway, scriptTask, …) — existing subclasses keep
 * compiling because every new method ships a no-op here first.
 *
 * Mirrors DTMPL's `AbstractAstNodeVisitor` precedent.
 */
abstract class AbstractWorkflowAstVisitor implements WorkflowAstVisitorInterface
{
    public function enterDefinition(ProcessDefinitionAst $def): void
    {
    }

    public function leaveDefinition(ProcessDefinitionAst $def): void
    {
    }

    public function visitStartEvent(StartEventAst $e): void
    {
    }

    public function visitEndEvent(EndEventAst $e): void
    {
    }

    public function visitExclusiveGateway(ExclusiveGatewayAst $g): void
    {
    }

    public function visitParallelGateway(ParallelGatewayAst $g): void
    {
    }

    public function visitInclusiveGateway(InclusiveGatewayAst $g): void
    {
    }

    public function visitEventBasedGateway(EventBasedGatewayAst $g): void
    {
    }

    public function visitUserTask(UserTaskAst $t): void
    {
    }

    public function visitServiceTask(ServiceTaskAst $t): void
    {
    }

    public function visitSubProcess(SubProcessAst $s): void
    {
    }

    public function visitCallActivity(CallActivityAst $c): void
    {
    }

    public function visitIntermediateTimerEvent(IntermediateTimerEventAst $e): void
    {
    }

    public function visitIntermediateMessageEvent(IntermediateMessageEventAst $e): void
    {
    }

    public function visitIntermediateSignalEvent(IntermediateSignalEventAst $e): void
    {
    }

    public function visitIntermediateSignalThrowEvent(IntermediateSignalThrowEventAst $e): void
    {
    }

    public function visitIntermediateMessageThrowEvent(IntermediateMessageThrowEventAst $e): void
    {
    }

    public function visitBoundaryEvent(BoundaryEventAst $b): void
    {
    }

    public function visitSequenceFlow(SequenceFlowAst $f): void
    {
    }
}
