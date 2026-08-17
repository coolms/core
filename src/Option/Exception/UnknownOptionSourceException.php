<?php

declare(strict_types=1);

namespace CoolMS\Core\Option\Exception;

use RuntimeException;

/**
 * Thrown when a caller asks
 * {@see \CoolMS\CoreModule\Option\OptionSourceRegistry} for a key
 * that no registered provider advertises. Surfaced as a 404 by the
 * options API endpoint.
 */
final class UnknownOptionSourceException extends RuntimeException
{
}
