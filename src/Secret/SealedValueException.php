<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * A sealed value that cannot be opened: malformed, truncated, tampered, or
 * sealed under a key this host does not hold. Deliberately one message for
 * the last three -- an authenticated box does not say which, and a caller
 * that could tell them apart would be a caller leaking key material.
 */
final class SealedValueException extends SecretStoreException
{
    public static function malformed(): self
    {
        return new self('Sealed value is not in a recognised form.');
    }

    public static function undecryptable(): self
    {
        return new self('Sealed value does not open under any key this host holds.');
    }
}
