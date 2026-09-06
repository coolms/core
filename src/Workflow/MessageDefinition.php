<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Message metadata for message-triggered start events, intermediate
 * message catch events, and boundary message catch events. The
 * `correlationKey` is the *name of a process variable* that the engine
 * matches incoming message envelopes against -- not the value itself.
 *
 * Validator rules enforced at deploy time:
 *  - `WF.MESSAGE_MISSING_NAME`           -- `$name` non-blank
 *  - `WF.MESSAGE_MISSING_CORRELATION`    -- `$correlationKey` non-blank
 *  - `WF.MESSAGE_CORRELATION_UNDECLARED` -- `$correlationKey` resolves
 *    to a key in `process.variables`
 *
 * See `docs/investigations/m2c-design.md` section 2.3 / section 5.5(h).
 */
final readonly class MessageDefinition
{
    public function __construct(
        public string $name,
        public string $correlationKey,
    ) {
    }
}
