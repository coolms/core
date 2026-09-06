<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\SignalDefinition;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * BPMN-Lite Intermediate Signal THROW Event — the fire-and-continue
 * counterpart of {@see IntermediateSignalEventAst} (the catch). When a
 * token reaches it, the engine BROADCASTS {@see $signal} to every parked
 * signal-catch token AND bootstraps every signal-start subscriber (the
 * same fan-out the external `POST /api/v1/workflow/signals` throw does),
 * then advances the token through its single outgoing flow. It does NOT
 * park — a throw waits for nothing.
 *
 * Source JSON uses `type: 'intermediateThrowEvent'` with
 * `subtype: 'signal'` — the throw mirror of the catch's
 * `intermediateCatchEvent` + `subtype: 'signal'`. The `intermediateThrowEvent`
 * spelling leaves room for future message / escalation throw subtypes
 * without reusing the catch type.
 *
 * **Broadcast, not point-to-point.** Like the catch, a signal throw
 * carries ONLY a name (no correlation key): the broadcast fans out to
 * ALL matching catchers across ALL running instances via the
 * `SignalBroadcasterInterface` in the consuming application. The
 * engine dispatches the broadcast as an async Messenger message (its own
 * committed unit of work, mirroring the timer-fire dispatch) so the
 * broadcast never nests inside — nor rolls back with — the throwing
 * token's advance transaction.
 */
final class IntermediateSignalThrowEventAst implements ElementInterface
{
    public ElementKind $kind {
        get => ElementKind::IntermediateSignalThrowEvent;
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
        $visitor->visitIntermediateSignalThrowEvent($this);
    }
}
