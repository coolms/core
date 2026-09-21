<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

use InvalidArgumentException;

use function sprintf;

/**
 * The host contracts in force in an installation: for each contract name,
 * the one installed theme that implements it.
 *
 * "Active" is a site notion -- the SSR theme a section renders with. A host
 * like the administration console is served by the theme that ships it,
 * which is installed and never "activated"; so what selects the modules'
 * entries for a contract is the installed theme that declares it, and there
 * may be only one. Two installed themes declaring the same contract are
 * refused by name, at install and again wherever this is built.
 */
final readonly class HostContracts
{
    /**
     * @param array<string, ThemeContracts> $byContract contract name -> the theme implementing it
     */
    private function __construct(
        public array $byContract,
    ) {
    }

    public static function none(): self
    {
        return new self([]);
    }

    /**
     * @param iterable<ThemeContracts> $themes every installed theme's declaration
     *
     * @throws InvalidArgumentException when two themes implement one contract
     */
    public static function fromThemes(iterable $themes): self
    {
        $byContract = [];
        foreach ($themes as $theme) {
            foreach ($theme->versions as $name => $version) {
                $prior = $byContract[$name] ?? null;
                if (null !== $prior && $prior->themeSlug !== $theme->themeSlug) {
                    throw new InvalidArgumentException(sprintf("Themes '%s' and '%s' both implement %s; an installation has one theme per host contract.", $prior->themeSlug, $theme->themeSlug, $name));
                }
                $byContract[$name] = $theme;
            }
        }

        return new self($byContract);
    }

    public function forContract(string $contract): ?ThemeContracts
    {
        return $this->byContract[$contract] ?? null;
    }

    /** @return array<string, string> contract name -> MAJOR.MINOR */
    public function versions(): array
    {
        $out = [];
        foreach ($this->byContract as $name => $theme) {
            $out[$name] = (string) $theme->versions[$name];
        }

        return $out;
    }

    /** @return array<string, string> contract name -> the implementing theme's slug */
    public function hosts(): array
    {
        $out = [];
        foreach ($this->byContract as $name => $theme) {
            $out[$name] = $theme->themeSlug;
        }

        return $out;
    }
}
