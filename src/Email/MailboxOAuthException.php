<?php

declare(strict_types=1);
namespace CoolMS\Core\Email;

use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Throwable;

use function sprintf;

/**
 * A fault in the OAuth mailbox-credential subsystem: a malformed provider
 * response, an unknown provider key, a mailbox with no sealed grant, or a grant
 * that can no longer be refreshed (the user must reconnect).
 *
 * All are domain-level; the transport / API layers decide how to surface them
 * (a fetch logs + skips, a connect endpoint would 422 / 502).
 */
final class MailboxOAuthException extends RuntimeException
{
    public static function unknownProvider(string $key): self
    {
        return new self(sprintf('No OAuth mail provider is registered for "%s".', $key));
    }

    public static function noTokens(Uuid $mailboxId): self
    {
        return new self(sprintf('Mailbox "%s" has no sealed OAuth credential.', $mailboxId->toRfc4122()));
    }

    /**
     * The access token has expired and there is no usable refresh token — the
     * user must re-run the OAuth consent flow to reconnect the mailbox.
     */
    public static function reauthRequired(Uuid $mailboxId): self
    {
        return new self(sprintf('Mailbox "%s" OAuth grant expired and cannot be refreshed; reconnect required.', $mailboxId->toRfc4122()));
    }

    public static function malformedTokenResponse(): self
    {
        return new self('OAuth token response is missing a usable access_token.');
    }

    /** The mailbox is not an OAuth mailbox, so there is nothing to connect. */
    public static function notOAuthMailbox(Uuid $mailboxId): self
    {
        return new self(sprintf('Mailbox "%s" is not an OAuth mailbox; nothing to connect.', $mailboxId->toRfc4122()));
    }

    /** The mailbox referenced by a connect state no longer exists. */
    public static function unknownMailbox(Uuid $mailboxId): self
    {
        return new self(sprintf('Mailbox "%s" referenced by the connect state was not found.', $mailboxId->toRfc4122()));
    }

    /** The mailbox's provider no longer matches the one the connect state was minted for. */
    public static function providerMismatch(Uuid $mailboxId): self
    {
        return new self(sprintf('Mailbox "%s" OAuth provider does not match the connect state.', $mailboxId->toRfc4122()));
    }

    /** The connect state is missing, malformed, or has been tampered with (fails to unseal). */
    public static function invalidConnectState(): self
    {
        return new self('The OAuth connect state is missing, malformed, or has been tampered with.');
    }

    /** The connect state is past its short validity window — the consent round-trip must restart. */
    public static function connectStateExpired(): self
    {
        return new self('The OAuth connect state has expired; restart the connect flow.');
    }

    public static function providerError(string $key, Throwable $previous): self
    {
        return new self(sprintf('OAuth provider "%s" request failed: %s', $key, $previous->getMessage()), 0, $previous);
    }
}
