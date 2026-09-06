<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Subtype axis for {@see \CoolMS\Core\Workflow\BoundaryEventAst}
 * per `docs/investigations/m2c-design.md` §2.3 / §3.1.
 *
 * Three flavours in scope:
 * - `timer`   — attaches to `userTask`, `serviceTask`, intermediate
 *               timer/message events, or `parallelGateway` (G8
 *               workaround per verification-design §3.3).
 * - `message` — non-interrupting only, attaches to `serviceTask` only
 *               (G7 promotion; enforced by `G7ScopeGuardRule`).
 * - `signal`  — BROADCAST catch attached to a host activity (userTask /
 *               serviceTask / intermediate timer or message event /
 *               parallelGateway per the same host-kind gate as timer).
 *               Interrupting OR non-interrupting (unlike message, which
 *               M2 restricted to non-interrupting). Fires when a matching
 *               signal is broadcast WHILE the host is live — the
 *               `SignalBroadcaster` finds live host tokens carrying a
 *               matching signal boundary and diverts via the subtype-
 *               agnostic `TokenAdvancer::fireBoundary`. The name is the
 *               sole routing key (signals carry no correlation key);
 *               `SignalDeclarationRule` enforces a non-blank name.
 * - `error`   — interrupting only, attaches to `serviceTask` only (F7
 *               / ADR-137 phase 4; enforced by `ErrorBoundaryScopeRule`).
 *               Catches a service-task handler failure and routes the
 *               token down the boundary's outgoing flow instead of
 *               failing the whole process instance.
 * - `compensation` — attaches to `serviceTask` only (F7 / ADR-137 phase 4;
 *               enforced by `CompensationScopeRule`). UNLIKE timer/message/
 *               error boundaries it does NOT fire on the host's lifecycle and
 *               does NOT divert a token during normal flow — it is purely an
 *               association marking the host compensable; its outgoing flow
 *               targets the activity's `forCompensation` undo handler, which
 *               the engine runs ONLY when a `compensate` end-throw is reached
 *               (reverse-order saga rollback).
 *
 * The `escalation` boundary subtype is still deferred (it propagates up a
 * subprocess scope hierarchy the engine does not yet model) and is
 * rejected by the parser's `WF.UNKNOWN_CONSTRUCT_TYPE` (the enum only
 * holds in-scope cases).
 */
enum BoundarySubtype: string
{
    case Timer = 'timer';
    case Message = 'message';
    case Signal = 'signal';
    case Error = 'error';
    case Compensation = 'compensation';
}
