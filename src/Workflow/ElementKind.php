<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Discriminator across every concrete BPMN-Lite AST element type per
 * `docs/investigations/m2c-design.md` §3.1.
 *
 * The backing string values are kebab/camel-cased to match the JSON
 * `type` field the parser reads on each `elements[]` entry (e.g.
 * `"type": "startEvent"`). Sequence flows and boundary events live in
 * their own value objects under ``Flow`\` and are
 * NOT carried as element kinds — they are dispatched separately by the
 * visitor.
 *
 * Constructs outside this closed set (`scriptTask`, `businessRuleTask`,
 * ...) trip `UnsupportedConstructRule` at validate time with code
 * `WF.UNSUPPORTED_CONSTRUCT`; truly unknown `type` strings
 * (`"userTAsk"`) trip the parser with code `WF.UNKNOWN_CONSTRUCT_TYPE`.
 */
enum ElementKind: string
{
    case StartEvent = 'startEvent';
    case EndEvent = 'endEvent';
    case ExclusiveGateway = 'exclusiveGateway';
    case ParallelGateway = 'parallelGateway';
    case InclusiveGateway = 'inclusiveGateway';
    case EventBasedGateway = 'eventBasedGateway';
    case UserTask = 'userTask';
    case ServiceTask = 'serviceTask';

    /**
     * Embedded subprocess -- a scope inside the SAME process instance.
     * Its children live in the same flat `elements[]` list and point
     * back with `"parent": "<subProcessId>"`; see {@see \CoolMS\Core\Workflow\SubProcessAst}
     * for why the AST stays flat.
     */
    case SubProcess = 'subProcess';

    /**
     * Call activity -- invokes another deployed definition as a SEPARATE
     * child `ProcessInstance` and waits for it. Contrast
     * {@see SubProcess}, which is a scope inside the same instance.
     */
    case CallActivity = 'callActivity';
    case IntermediateTimerEvent = 'intermediateTimerEvent';
    case IntermediateMessageEvent = 'intermediateMessageEvent';
    case IntermediateSignalEvent = 'intermediateSignalEvent';
    case IntermediateSignalThrowEvent = 'intermediateSignalThrowEvent';
    case IntermediateMessageThrowEvent = 'intermediateMessageThrowEvent';
    case IntermediateConditionalEvent = 'intermediateConditionalEvent';
}
