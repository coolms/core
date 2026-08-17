<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use RuntimeException;

/**
 * Thrown when a secret backend itself fails -- a misconfigured or missing
 * master key, an undecryptable / tampered secrets file, an unreachable
 * Vault, etc. Distinct from {@see SecretNotFoundException} (a normal
 * "this key isn't set" on `getRequired()`): a `SecretStoreException` means
 * the backend cannot operate at all and is a hard configuration error.
 */
class SecretStoreException extends RuntimeException
{
}
