<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * F1 -- platform secret-store seam.
 *
 * The single read port through which the platform fetches sensitive
 * material (connector API tokens, OAuth refresh tokens, webhook signing
 * keys, PGP private keys, ...). Consumers depend on this interface, never
 * on a concrete backend, so a deployment can swap `$_ENV` for an encrypted
 * file or HashiCorp Vault without touching call sites.
 *
 * The active backend is chosen per-environment via
 * `coolms_core.secret_store.driver`; the Core Extension aliases this
 * interface to the selected implementation. F1.a ships the dev-default
 * {@see \CoolMS\CoreBundle\Secret\EnvSecretStore}; the libsodium
 * filesystem store and the Vault store land in later F1 slices.
 *
 * Keys are logical names (`stripe_api_key`, `telegram_bot_token`); each
 * backend maps them to its own physical lookup (env-var name, file key,
 * Vault path). A key that is unset OR resolves to an empty string is
 * treated as ABSENT -- an empty secret is a misconfiguration, never a
 * valid value, so callers never accidentally send a blank credential.
 */
interface SecretStoreInterface
{
    /**
     * Resolve a secret, or NULL when it is absent (unset or empty).
     */
    public function get(string $key): ?string;

    /**
     * Whether a non-empty secret is present for the key.
     */
    public function has(string $key): bool;

    /**
     * Resolve a secret, failing loud when it is absent.
     *
     * Use this wherever a missing secret must abort the operation
     * rather than silently degrade (e.g. an HTTP connector's
     * `Authorization` header must never be sent blank).
     *
     * @throws SecretNotFoundException when the key is unset or empty
     */
    public function getRequired(string $key): string;
}
