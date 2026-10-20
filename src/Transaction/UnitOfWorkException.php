<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

use RuntimeException;

/**
 * A unit of work used out of order -- opened twice, committed while none is open or while a flush runs -- or a commit
 * that failed. The owner turns it into a refusal: a 500, a failed message, a non-zero exit.
 */
final class UnitOfWorkException extends RuntimeException
{
}
