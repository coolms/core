<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * What a theme declares it implements: host contracts by name, each at one
 * MAJOR.MINOR version, on one framework. Read from the theme's manifest
 * (`contracts:` in theme.yaml) by whoever installs, activates or serves it.
 *
 * A declaration nothing reads is a placeholder, which the platform forbids; this
 * object exists so the readers share one parse and one refusal.
 */
final readonly class ThemeContracts
{
    /**
     * @param array<string, string> $versions contract name -> MAJOR.MINOR
     */
    public function __construct(
        public string $themeSlug,
        public string $framework,
        public array $versions,
    ) {
        foreach ($versions as $name => $version) {
            if (1 !== preg_match('/^\d+\.\d+$/', (string) $version)) {
                throw new InvalidArgumentException(sprintf("Theme '%s' declares %s '%s'; a contract version is MAJOR.MINOR.", $themeSlug, (string) $name, (string) $version));
            }
        }
    }

    public function declares(string $contract): bool
    {
        return isset($this->versions[$contract]);
    }

    public function versionOf(string $contract): ?string
    {
        return $this->versions[$contract] ?? null;
    }
}
