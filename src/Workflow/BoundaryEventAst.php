<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\BoundarySubtype;
use CoolMS\Core\Workflow\ErrorDefinition;
use CoolMS\Core\Workflow\MessageDefinition;
use CoolMS\Core\Workflow\SignalDefinition;
use CoolMS\Core\Workflow\TimerDefinition;

/**
 * Immutable AST node for a BPMN boundary event — a timer or message
 * trigger attached to a host activity (userTask / serviceTask / etc.)
 * that diverts the token along an alternative path when it fires.
 *
 * Boundary events are NOT elements — they live in their own VO and
 * are stored flat on {@see \CoolMS\Core\Workflow\ProcessDefinitionAst::$boundaryEvents}
 * (design doc §3.5 row "Where do boundary events live?"). The
 * `$attachedToId` carries the host element id; the validator's
 * `BoundaryAttachmentRule` in the consuming application
 * + `G7ScopeGuardRule` in the consuming application
 * enforce the per-subtype host-kind constraints (G7 promotion +
 * G8 parallelGateway workaround).
 *
 * `$interrupting` collapses the JSON's `interrupting` / `nonInterrupting`
 * dual flags into a single bool (default true). The parser raises
 * `WF.BOUNDARY_CONTRADICTORY_FLAGS` if both keys are set with
 * conflicting values (design doc §4.1 step 3).
 *
 * Exactly one of `$message` / `$timer` / `$error` / `$signal` is non-null
 * at parse time — which one depends on `$subtype`. The TimerSpecRule /
 * MessageCorrelationRule / ErrorBoundaryScopeRule / SignalDeclarationRule
 * validators enforce internal shape + host constraints.
 *
 * `$outgoingFlowId` is the single sequence flow leaving the boundary
 * event (boundary events have no incoming flows — they're triggered
 * by the host's lifecycle, not by token flow).
 */
final readonly class BoundaryEventAst
{
    public function __construct(
        public string $id,
        public ?string $name,
        public BoundarySubtype $subtype,
        public string $attachedToId,
        public bool $interrupting = true,
        public ?MessageDefinition $message = null,
        public ?TimerDefinition $timer = null,
        public string $outgoingFlowId = '',
        public ?SourceLocation $sourceLocation = null,
        // The ERROR-boundary payload (F7 / ADR-137 phase 4); non-null
        // only when `$subtype` is BoundarySubtype::Error. Appended last
        // (defaulted) so existing positional callers are unaffected.
        public ?ErrorDefinition $error = null,
        // The SIGNAL-boundary payload (#1631); non-null only when
        // `$subtype` is BoundarySubtype::Signal. Carries the broadcast
        // name (signals have no correlation key). Appended last +
        // defaulted so existing positional callers are unaffected.
        public ?SignalDefinition $signal = null,
        // NOTE: $outgoingFlowId has a default ('') so PHP 8.5 doesn't
        // deprecate the optional-before-required parameter order. The
        // parser ALWAYS passes a non-empty value via named args; the
        // empty-string default is unreachable in production.
    ) {
    }
}
