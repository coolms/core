<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use function sprintf;

/**
 * The master key itself is unusable: absent, not base64, the wrong length.
 * Named per cause so a caller can tell "configure a key" from "the key you
 * configured is broken" -- the two have different remedies.
 */
final class MasterKeyException extends SecretStoreException
{
    public static function notConfigured(string $where): self
    {
        return new self(sprintf('Master key "%s" is not set.', $where));
    }

    public static function notBase64(): self
    {
        return new self('Master key is not valid base64.');
    }

    public static function wrongLength(int $actual): self
    {
        return new self(sprintf('Master key must decode to %d bytes, got %d.', MasterKey::BYTES, $actual));
    }

    public static function invalid(string $where, self $cause): self
    {
        return new self(sprintf('Master key "%s" is invalid: %s', $where, $cause->getMessage()), 0, $cause);
    }
}
