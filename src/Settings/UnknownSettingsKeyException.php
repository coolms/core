<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

use InvalidArgumentException;

use function sprintf;

/**
 * A settings key nobody declared.
 *
 * Thrown rather than silently returning empty, because the two mean opposite
 * things: an undeclared key is a caller mistake (or an attempt to reach a config
 * row that is not a settings block), while a declared key with nothing saved is
 * the ordinary first-visit case.
 */
final class UnknownSettingsKeyException extends InvalidArgumentException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('No module declares a settings block with the key "%s".', $key));
    }
}
