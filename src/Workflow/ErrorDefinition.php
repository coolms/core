<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Error metadata for an ERROR boundary event — the BPMN
 * `errorEventDefinition` carried by a {@see \CoolMS\Core\Workflow\BoundaryEventAst}
 * whose subtype is {@see \CoolMS\Core\Workflow\BoundarySubtype::Error}.
 *
 * `$code` mirrors BPMN's `errorRef` / error code. It is OPTIONAL: an empty code is
 * a **catch-all** that handles ANY service-task handler failure on the host
 * activity. A non-empty code is parsed, validated, and recorded for display, but
 * per-code routing is a follow-up — the engine treats every error boundary as
 * catch-all in this slice because `ServiceTaskHandlerException` in the consuming application
 * does not yet carry a typed BPMN error code (handlers would need to emit one).
 *
 * Deliberately simpler than {@see MessageDefinition} / {@see TimerDefinition} — an
 * error event has no correlation key and no schedule, only an optional code.
 */
final readonly class ErrorDefinition
{
    public function __construct(
        public string $code = '',
    ) {
    }
}
