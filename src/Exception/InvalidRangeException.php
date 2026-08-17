<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

use InvalidArgumentException;

/**
 * Raised by the shared range / time value objects in
 * `CoolMS\Core\ValueObject` ({@see \CoolMS\Core\ValueObject\TimeOfDay},
 * {@see \CoolMS\Core\ValueObject\DateRange},
 * {@see \CoolMS\Core\ValueObject\DateTimeRange},
 * {@see \CoolMS\Core\ValueObject\TimeRange}) when a constructor
 * receives values that violate the VO's invariants
 * (out-of-bounds fields, inverted bounds, malformed string literals,
 * malformed JSON wire shape, etc.).
 *
 * Subclass of `\InvalidArgumentException` so callers that already
 * catch the generic SPL form keep working.
 */
final class InvalidRangeException extends InvalidArgumentException
{
}
