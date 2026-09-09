<?php

declare(strict_types=1);
namespace CoolMS\Core\Scheduler;

use RuntimeException;

/**
 * Raised by a {@see \CoolMS\Core\Scheduler\TriggerInterface}
 * implementation when its `triggerSpec` is unparseable / invalid for
 * that kind (e.g. a malformed cron expression, an RRULE with FREQ=DAYLY).
 *
 * Translated into HTTP 422 by the API Platform Processors via the
 * surrounding Application service so invalid specs are surfaced
 * cleanly to admin UI consumers.
 */
final class InvalidTriggerSpecException extends RuntimeException
{
}
