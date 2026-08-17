<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use InvalidArgumentException;

/**
 * Thrown when an {@see OutboxMessage} is constructed with invalid data (e.g. an
 * empty type). Mirrors {@see \CoolMS\Core\Analytics\InvalidAnalyticsEventException}.
 */
final class InvalidOutboxMessageException extends InvalidArgumentException
{
}
