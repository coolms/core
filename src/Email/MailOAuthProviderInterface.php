<?php

declare(strict_types=1);
namespace CoolMS\Core\Email;

/**
 * M8.f — the outbound port to an OAuth2 mail provider (Gmail today; Microsoft
 * Graph = M8.g slots in as another tagged impl). Purpose-built for MAIL access
 * (the `https://mail.google.com/` full-IMAP/SMTP scope), distinct from the SSO
 * login providers which request identity scopes and store tokens in the session.
 *
 * Implementations are tagged `coolms.email.oauth_provider` (autoconfigured by
 * interface in the Email Extension) and resolved by {@see key()} through
 * `MailOAuthProviderRegistry` in the consuming application.
 *
 * The authorization-code flow: {@see buildAuthorizationUrl()} sends the user to
 * consent; the callback hands the returned `code` to {@see exchangeCode()} for
 * the initial grant; {@see refresh()} renews an expired access token from its
 * refresh token. Token EXPIRY is anchored to the implementation's own clock.
 */
interface MailOAuthProviderInterface
{
    /** The stable provider key (e.g. `google`) — matches `Mailbox::$oauthProvider`. */
    public function key(): string;

    /**
     * Build the provider consent URL. `$state` is the caller-minted CSRF nonce
     * (validated on callback); `$redirectUri` must match the one registered with
     * the provider AND replayed to {@see exchangeCode()}.
     */
    public function buildAuthorizationUrl(string $redirectUri, string $state): string;

    /**
     * Exchange an authorization `code` for the initial token grant.
     *
     * @throws MailboxOAuthException on a transport / provider / parse failure
     */
    public function exchangeCode(string $code, string $redirectUri): OAuthTokens;

    /**
     * Renew an access token from its refresh token. The response may omit a fresh
     * refresh token; callers fold via {@see OAuthTokens::withRefreshedFrom()}.
     *
     * @throws MailboxOAuthException on a transport / provider / parse failure
     */
    public function refresh(string $refreshToken): OAuthTokens;
}
