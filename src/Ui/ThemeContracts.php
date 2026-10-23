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
 *
 * Names are held canonical ({@see ContractName}): a theme declaring `console`
 * implements `admin`. One that declares a contract under both of its names is
 * refused -- it would implement one contract twice.
 */
final readonly class ThemeContracts
{
    /** @var array<string, string> canonical contract name -> MAJOR.MINOR */
    public array $versions;

    /** @var array<string, string> canonical contract name -> the name as the theme wrote it */
    public array $declaredNames;

    /**
     * @param array<string, string> $versions contract name -> MAJOR.MINOR, a deprecated name read as its new one
     */
    public function __construct(
        public string $themeSlug,
        public string $framework,
        array $versions,
    ) {
        $canonical = [];
        $declared = [];
        foreach ($versions as $name => $version) {
            $name = (string) $name;
            if (1 !== preg_match('/^\d+\.\d+$/', (string) $version)) {
                throw new InvalidArgumentException(sprintf("Theme '%s' declares %s '%s'; a contract version is MAJOR.MINOR.", $themeSlug, $name, (string) $version));
            }
            $now = ContractName::canonical($name);
            if (isset($declared[$now])) {
                throw new InvalidArgumentException(sprintf("Theme '%s' declares %s and %s, one contract under two names; declare %s alone.", $themeSlug, $declared[$now], $name, $now));
            }
            $canonical[$now] = (string) $version;
            $declared[$now] = $name;
        }
        $this->versions = $canonical;
        $this->declaredNames = $declared;
    }

    public function declares(string $contract): bool
    {
        return isset($this->versions[ContractName::canonical($contract)]);
    }

    public function versionOf(string $contract): ?string
    {
        return $this->versions[ContractName::canonical($contract)] ?? null;
    }
}
