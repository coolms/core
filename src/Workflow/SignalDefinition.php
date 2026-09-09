<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Signal metadata for intermediate signal catch events (and, in a
 * future slice, signal-start + boundary signal events).
 *
 * Unlike a {@see MessageDefinition}, a signal carries ONLY a `name` --
 * there is no correlation key. Signals are BROADCAST: one thrown
 * signal resumes EVERY token parked on that name across every running
 * instance, regardless of per-instance variables (BPMN 2.0 section 10.5.5 /
 * Camunda 7 "signal is a broadcast, message is point-to-point"). The
 * name is the sole routing key.
 *
 * Validator rule enforced at deploy time:
 *  - `WF.SIGNAL_MISSING_NAME` -- `$name` non-blank
 *    (see `SignalDeclarationRule` in the consuming application).
 */
final readonly class SignalDefinition
{
    public function __construct(
        public string $name,
    ) {
    }
}
