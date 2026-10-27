<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

use InvalidArgumentException;

use function preg_match;
use function trim;

/**
 * Why one settings value cannot be in force, said to the admin who would save it.
 *
 * `field` names the key inside the block, so a client can place the reason beside its control. `code` is stable for
 * a client to branch on or translate; `reason` is the sentence the admin reads, written by the module that owns the
 * rule because only it knows what would make the value acceptable.
 */
final readonly class SettingsValueRefusal
{
    /** Lowercase words joined by dots or underscores, e.g. `identifier.no_confirmation_channel`. */
    public const string CODE_PATTERN = '/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*$/';

    public function __construct(
        /** The key inside the block, e.g. `phone`. */
        public string $field,
        public string $code,
        /** What is wrong and what would make it acceptable, e.g. "Phone needs an SMS channel to send its codes." */
        public string $reason,
    ) {
        if ('' === trim($field) || '' === trim($reason)) {
            throw new InvalidArgumentException('A settings refusal names its field and says why.');
        }
        if (1 !== preg_match(self::CODE_PATTERN, $code)) {
            throw new InvalidArgumentException('A settings refusal code is lowercase words joined by dots or underscores.');
        }
    }
}
