<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\SignalDefinition;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Intermediate Signal Catch Event -- a flow-on-the-wire pause
 * that resumes when a matching signal is BROADCAST. {@see $signal}
 * carries the name only (signals have no correlation key); the
 * validator checks the name is present (`WF.SIGNAL_MISSING_NAME`).
 *
 * Source JSON uses `type: 'intermediateCatchEvent'` with
 * `subtype: 'signal'` -- the same dual-spelling shape as the timer /
 * message catch events. The parser distinguishes `boundaryEvent`s by
 * the presence of `attachedTo`; without it, the event becomes this
 * on-the-wire intermediate catch.
 *
 * **Broadcast vs point-to-point.** A message catch
 * ({@see IntermediateMessageEventAst}) is resumed point-to-point by the
 * `MessageCorrelatorInterface` in the consuming application using a
 * correlation key; a signal catch is resumed by the
 * `SignalBroadcasterInterface` in the consuming application, which
 * resumes EVERY parked token matching the name.
 */
final class IntermediateSignalEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateSignalEvent;
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
        public readonly SignalDefinition $signal,
        public readonly ?SourceLocation $sourceLocation = null,
    ) {
    }

    public function accept(WorkflowAstVisitorInterface $visitor): void
    {
        $visitor->visitIntermediateSignalEvent($this);
    }
}
