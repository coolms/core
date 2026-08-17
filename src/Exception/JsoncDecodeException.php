<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

use JsonException;
use Throwable;

use function sprintf;

/**
 * Raised by {@see \CoolMS\CoreModule\Json\JsoncDecoderInterface}
 * when a JSONC source either (a) fails to parse as JSON after comment
 * stripping or (b) decodes to a non-array root.
 *
 * **Why a typed exception, not a bare `JsonException`.** Consumers
 * (BPMN parsers, future config loaders, fixture readers) want to
 * distinguish "the file was malformed" from "the application logic
 * threw mid-parse" without sniffing class names from the
 * `vendor/symfony/serializer` tree. The typed exception also carries
 * the optional `$sourcePath` so error messages can name the offending
 * file -- the JSONC decoder doesn't know paths, but the caller
 * does and threads it in via the second arg to `decode()`.
 *
 * The underlying `JsonException` (when present) is wired in as the
 * `$previous` so stack traces stay walkable.
 */
class JsoncDecodeException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $sourcePath = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function malformedJson(JsonException $cause, ?string $sourcePath = null): self
    {
        $msg = null !== $sourcePath
            ? sprintf('Malformed JSON in "%s": %s', $sourcePath, $cause->getMessage())
            : sprintf('Malformed JSON: %s', $cause->getMessage());

        return new self($msg, $sourcePath, $cause);
    }

    public static function nonArrayRoot(?string $sourcePath = null): self
    {
        $msg = null !== $sourcePath
            ? sprintf('JSONC root in "%s" must be a JSON object or array.', $sourcePath)
            : 'JSONC root must be a JSON object or array.';

        return new self($msg, $sourcePath);
    }
}
