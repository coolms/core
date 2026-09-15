<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use function sprintf;

/**
 * The master key itself is unusable: absent, not base64, the wrong length.
 * Carries the reason and where the key was looked for, so a caller can tell
 * "configure a key" from "the key you configured is broken" -- the two have
 * different remedies -- and can name the variable in its own words.
 */
final class MasterKeyException extends SecretStoreException
{
    public const string NOT_CONFIGURED = 'not_configured';

    public const string INVALID = 'invalid';

    private function __construct(
        public readonly string $reason,
        public readonly string $where,
        string $message,
        ?self $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function notConfigured(string $where): self
    {
        return new self(self::NOT_CONFIGURED, $where, sprintf('Master key "%s" is not set.', $where));
    }

    public static function notBase64(): self
    {
        return new self(self::INVALID, '', 'Master key is not valid base64.');
    }

    public static function wrongLength(int $actual): self
    {
        $message = sprintf('Master key must decode to %d bytes, got %d.', MasterKey::BYTES, $actual);

        return new self(self::INVALID, '', $message);
    }

    /** The same fault, now knowing where the key came from. */
    public static function invalid(string $where, self $cause): self
    {
        $message = sprintf('Master key "%s" is invalid: %s', $where, $cause->getMessage());

        return new self(self::INVALID, $where, $message, $cause);
    }

    public function isNotConfigured(): bool
    {
        return self::NOT_CONFIGURED === $this->reason;
    }
}
