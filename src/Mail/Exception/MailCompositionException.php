<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

/**
 * The message could not be turned into an email — a body that fails to render,
 * a theme layout that is missing and has no usable fallback.
 *
 * Scope is deliberately narrow. A missing IMAGE or an unreadable ATTACHMENT does
 * NOT raise this: the composer degrades (leaves the `<img>` alone, drops the
 * attachment and logs it) because one broken asset is not a reason to abandon a
 * campaign to thousands of recipients. Only a failure that would produce a
 * meaningless email reaches here.
 */
final class MailCompositionException extends RuntimeException
{
    /**
     * The cause is folded into the MESSAGE, not just chained as `previous`.
     * Messenger logs the top-level message only, so a campaign that dies on the
     * worker otherwise leaves an operator with "could not be rendered" and no
     * hint whether it was a bad widget, a missing asset or a syntax error.
     */
    public static function bodyRenderFailed(?Throwable $previous = null): self
    {
        return new self(
            sprintf('The mail body could not be rendered.%s', self::because($previous)),
            0,
            $previous,
        );
    }

    public static function templateNotFound(string $template): self
    {
        return new self(sprintf(
            'Email layout "%s" was not found in the active theme and no platform default is available.',
            $template,
        ));
    }

    public static function templateRenderFailed(string $template, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Email layout "%s" could not be rendered.%s', $template, self::because($previous)),
            0,
            $previous,
        );
    }

    private static function because(?Throwable $previous): string
    {
        return null === $previous ? '' : sprintf(' Cause: %s: %s', $previous::class, $previous->getMessage());
    }
}
