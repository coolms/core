<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail\Exception;

use InvalidArgumentException;

/**
 * A {@see \CoolMS\Core\Mail\RichMailMessage} or one of its attachments was
 * constructed with input that can never produce a valid email.
 *
 * These are programming errors (an empty subject, an attachment with neither a
 * path nor bytes), not delivery failures — a transport problem surfaces from
 * the sender instead, so Messenger can retry it.
 */
final class InvalidRichMailException extends InvalidArgumentException
{
    public static function emptySubject(): self
    {
        return new self('A rich mail message needs a non-empty subject.');
    }

    public static function emptyRecipient(): self
    {
        return new self('A rich mail message needs at least one recipient.');
    }

    public static function emptyAttachmentFilename(): self
    {
        return new self('An attachment needs a non-empty filename.');
    }

    public static function emptyAttachmentPath(): self
    {
        return new self('An attachment read from the VFS needs a non-empty path.');
    }

    public static function emptyContentId(): self
    {
        return new self('An inline attachment needs a non-empty Content-ID.');
    }

    public static function missingSender(): self
    {
        return new self(
            'A rich mail message needs a From address. Sender identity belongs to the '
            . 'calling module (a campaign sender has its own configured from), not to the composer.',
        );
    }
}
