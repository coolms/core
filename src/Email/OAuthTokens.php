<?php

declare(strict_types=1);
namespace CoolMS\Core\Email;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use JsonException;

use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * M8.f — an OAuth2 grant for a mailbox: the current access token, the (optional)
 * refresh token used to renew it, the access token's expiry, and the granted
 * scope. Immutable; the sealed-at-rest form is the JSON of {@see toJson()} sealed
 * by `EmailCipherInterface` in the consuming application.
 *
 * `expiresAt` null = expiry unknown → treated as non-expiring (best-effort; a
 * provider that omits `expires_in` is trusted until a live 401 forces a reconnect
 * — the transport layer's concern, M8.f.2). `refreshToken` null = a one-shot
 * grant (e.g. a provider that didn't return one) → not renewable in place.
 */
final readonly class OAuthTokens
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken,
        public ?DateTimeImmutable $expiresAt,
        public ?string $scope,
    ) {
    }

    /**
     * Parse a provider token endpoint response (`access_token` / `expires_in` /
     * `refresh_token` / `scope`). `$now` anchors the absolute `expiresAt` so the
     * caller controls the clock (deterministic in tests).
     *
     * @param array<string, mixed> $data
     *
     * @throws MailboxOAuthException when no usable access_token is present
     */
    public static function fromTokenResponse(array $data, DateTimeImmutable $now): self
    {
        $accessToken = $data['access_token'] ?? null;
        if (!is_string($accessToken) || '' === $accessToken) {
            throw MailboxOAuthException::malformedTokenResponse();
        }

        $expiresIn = isset($data['expires_in']) && is_numeric($data['expires_in']) ? (int) $data['expires_in'] : null;
        $expiresAt = null !== $expiresIn ? $now->add(new DateInterval('PT' . $expiresIn . 'S')) : null;

        $refreshToken = isset($data['refresh_token']) && is_string($data['refresh_token']) && '' !== $data['refresh_token']
            ? $data['refresh_token']
            : null;
        $scope = isset($data['scope']) && is_string($data['scope']) ? $data['scope'] : null;

        return new self($accessToken, $refreshToken, $expiresAt, $scope);
    }

    /**
     * @throws MailboxOAuthException on a malformed blob (unparseable JSON or a
     *                               missing access token)
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw MailboxOAuthException::providerError('storage', $e);
        }

        if (!is_array($data)) {
            throw MailboxOAuthException::malformedTokenResponse();
        }

        $accessToken = $data['access_token'] ?? null;
        if (!is_string($accessToken) || '' === $accessToken) {
            throw MailboxOAuthException::malformedTokenResponse();
        }

        $expiresAtRaw = $data['expires_at'] ?? null;
        $expiresAt = is_string($expiresAtRaw) && '' !== $expiresAtRaw ? new DateTimeImmutable($expiresAtRaw) : null;
        $refreshToken = isset($data['refresh_token']) && is_string($data['refresh_token']) && '' !== $data['refresh_token']
            ? $data['refresh_token']
            : null;
        $scope = isset($data['scope']) && is_string($data['scope']) ? $data['scope'] : null;

        return new self($accessToken, $refreshToken, $expiresAt, $scope);
    }

    /**
     * True when the access token is at/near expiry (a `$skewSeconds` safety
     * margin renews slightly early so an in-flight request never races the cutoff).
     */
    public function isExpired(DateTimeImmutable $now, int $skewSeconds = 60): bool
    {
        if (null === $this->expiresAt) {
            return false;
        }

        return $this->expiresAt->getTimestamp() - $skewSeconds <= $now->getTimestamp();
    }

    public function hasRefreshToken(): bool
    {
        return null !== $this->refreshToken && '' !== $this->refreshToken;
    }

    /**
     * Fold a refresh response onto the prior grant. Providers (Google included)
     * OMIT the refresh token on a refresh response, so carry the old one forward
     * — losing it would strand the mailbox on the next expiry.
     */
    public function withRefreshedFrom(self $refreshed): self
    {
        return new self(
            $refreshed->accessToken,
            $refreshed->refreshToken ?? $this->refreshToken,
            $refreshed->expiresAt,
            $refreshed->scope ?? $this->scope,
        );
    }

    public function toJson(): string
    {
        return json_encode([
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt?->format(DateTimeInterface::ATOM),
            'scope' => $this->scope,
        ], JSON_THROW_ON_ERROR);
    }
}
