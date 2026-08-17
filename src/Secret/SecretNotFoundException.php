<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

use RuntimeException;

use function sprintf;

/**
 * Thrown by {@see SecretStoreInterface::getRequired()} when a required
 * secret is unset or empty in the active backend.
 *
 * The key is exposed (it is a logical name like `stripe_api_key`, never
 * the secret value) so operators can see WHICH secret is missing; the
 * resolved value is, of course, never included.
 */
final class SecretNotFoundException extends RuntimeException
{
    public function __construct(public readonly string $key)
    {
        parent::__construct(sprintf('Required secret "%s" is not configured.', $key));
    }
}
