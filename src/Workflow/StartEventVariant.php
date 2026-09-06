<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Variant axis for {@see \CoolMS\Core\Workflow\StartEventAst}.
 *
 * Closed enumeration of the start-event flavours the engine supports:
 * `none` (plain process start), `message` (correlation-keyed),
 * `timer` (duration / date / cycle), and `signal` (broadcast,
 * — bootstraps a fresh instance for EVERY subscriber when the
 * named signal is broadcast). The remaining BPMN start-event variants
 * (error, escalation, conditional, ...) stay deferred to a later ship;
 * the parser falls their unknown `variant` string back to `None`.
 */
enum StartEventVariant: string
{
    case None = 'none';
    case Message = 'message';
    case Timer = 'timer';
    case Signal = 'signal';
}
