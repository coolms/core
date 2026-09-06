<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\MessageDefinition;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Intermediate Message THROW Event — the fire-and-continue
 * counterpart of {@see IntermediateMessageEventAst} (the catch), and the
 * point-to-point sibling of {@see IntermediateSignalThrowEventAst}. When a
 * token reaches it, the engine CORRELATES the message to the ONE parked
 * message-catcher whose correlation-key variable matches (or bootstraps a
 * message-start), via the same `MessageCorrelatorInterface` in the consuming application
 * the external inbound-message path uses, then advances the token through
 * its single outgoing flow. It does NOT park — a throw waits for nothing.
 *
 * Source JSON uses `type: 'intermediateThrowEvent'` with
 * `subtype: 'message'` — the throw mirror of the catch's
 * `intermediateCatchEvent` + `subtype: 'message'`.
 *
 * **Point-to-point, not broadcast.** Unlike a signal throw, a message
 * carries a {@see MessageDefinition::$correlationKey} — the NAME of a
 * process variable. At throw time the engine resolves that variable on the
 * THROWING instance to a key VALUE and correlates on
 * `(messageName, keyValue)`, matching only catchers whose own
 * correlation-key variable resolves to the same value (Camunda 7
 * single-key correlation).
 */
final class IntermediateMessageThrowEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateMessageThrowEvent;
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
        public readonly MessageDefinition $message,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitIntermediateMessageThrowEvent($this);
    }
}
