<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use InvalidArgumentException;

/**
 * Thrown when an {@see AnalyticsEvent} is constructed with a malformed `type`
 * (the only structurally-invariant field).
 */
final class InvalidAnalyticsEventException extends InvalidArgumentException
{
}
