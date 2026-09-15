<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use function base64_decode;
use function hash;
use function strlen;
use function substr;

/**
 * One at-rest master key: 32 raw bytes and the id a sealed value carries to
 * name the key it was sealed under.
 *
 * The id is the first 16 hex characters of the key's SHA-256. It is derived,
 * so every host holding the same key computes the same id without being told
 * it, and it reveals nothing about the key. Two keys are the same key when
 * their ids match.
 *
 * The bytes are held for the life of the object; a ring keeps its keys for
 * the life of the process, which is also how long the process can read.
 */
final readonly class MasterKey
{
    public const int BYTES = 32;

    public string $id;

    /** @param non-empty-string $bytes exactly {@see BYTES} raw bytes */
    public function __construct(private string $bytes)
    {
        if (self::BYTES !== strlen($bytes)) {
            throw MasterKeyException::wrongLength(strlen($bytes));
        }
        $this->id = substr(hash('sha256', $bytes), 0, 16);
    }

    /** The base64 spelling the environment carries. */
    public static function fromBase64(string $encoded): self
    {
        $bytes = base64_decode($encoded, true);
        if (false === $bytes || '' === $bytes) {
            throw MasterKeyException::notBase64();
        }

        return new self($bytes);
    }

    /** @return non-empty-string */
    public function bytes(): string
    {
        return $this->bytes;
    }

    public function is(self $other): bool
    {
        return $this->id === $other->id;
    }
}
