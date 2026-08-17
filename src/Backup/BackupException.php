<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

use RuntimeException;

/**
 * Thrown on any backup/restore failure: an unsafe table identifier, a missing or
 * malformed bundle, an incompatible bundle format version, or an unreadable
 * payload. Surfaced by the `coolms:backup:*` commands as a clean error.
 */
final class BackupException extends RuntimeException
{
}
