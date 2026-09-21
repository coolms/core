<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

use function array_filter;
use function array_values;
use function sprintf;

/**
 * The platform matches a theme's declared contracts against the modules'
 * entries (the platform rule: hosts implement contracts, modules offer
 * entries). Pure: the callers -- the theme installer and activator,
 * `coolms:install`, the app-config manifest -- read the theme and the
 * catalogue and hand both here, so the three cannot disagree on a verdict.
 *
 * A theme that declares nothing leaves every entry unused; that is the
 * control which shows the declaration is what is read. Delete the theme's
 * `contracts:` block and a refusal turns into "unused", never into a merge.
 */
final class UiContractMatcher
{
    /**
     * @param iterable<UiEntry> $entries
     *
     * @return list<UiEntryVerdict>
     */
    public function match(?ThemeContracts $theme, iterable $entries): array
    {
        $verdicts = [];
        foreach ($entries as $entry) {
            $verdicts[] = $this->one($theme, $entry);
        }

        return $verdicts;
    }

    /**
     * @param iterable<UiEntry> $entries
     *
     * @return list<UiEntryVerdict> only the refusals, for the caller that stops on any
     */
    public function refusals(?ThemeContracts $theme, iterable $entries): array
    {
        return array_values(array_filter(
            $this->match($theme, $entries),
            static fn (UiEntryVerdict $v): bool => UiVerdict::Refused === $v->verdict,
        ));
    }

    /**
     * Every entry against the installation's host contracts: each entry
     * meets the one theme that implements its contract, or is unused when
     * no installed theme does.
     *
     * @param iterable<UiEntry> $entries
     *
     * @return list<UiEntryVerdict>
     */
    public function matchAll(HostContracts $hosts, iterable $entries): array
    {
        $verdicts = [];
        foreach ($entries as $entry) {
            $verdicts[] = $this->one($hosts->forContract($entry->contract), $entry);
        }

        return $verdicts;
    }

    /**
     * @param iterable<UiEntry> $entries
     *
     * @return list<UiEntryVerdict> only the refusals
     */
    public function refusalsAll(HostContracts $hosts, iterable $entries): array
    {
        return array_values(array_filter(
            $this->matchAll($hosts, $entries),
            static fn (UiEntryVerdict $v): bool => UiVerdict::Refused === $v->verdict,
        ));
    }

    private function one(?ThemeContracts $theme, UiEntry $entry): UiEntryVerdict
    {
        if (null === $theme) {
            return new UiEntryVerdict($entry, UiVerdict::Unused, sprintf(
                "Module '%s' offers %s %s for %s; no installed theme implements it -- unused.",
                $entry->module,
                $entry->contract,
                $entry->range,
                $entry->framework,
            ));
        }

        $version = $theme->versionOf($entry->contract);
        if (null === $version) {
            return new UiEntryVerdict($entry, UiVerdict::Unused, sprintf(
                "Module '%s' offers %s %s for %s; theme '%s' implements no %s -- unused.",
                $entry->module,
                $entry->contract,
                $entry->range,
                $entry->framework,
                $theme->themeSlug,
                $entry->contract,
            ));
        }

        if ($theme->framework !== $entry->framework) {
            return new UiEntryVerdict($entry, UiVerdict::Unused, sprintf(
                "Module '%s' offers %s %s for %s; theme '%s' is %s -- unused.",
                $entry->module,
                $entry->contract,
                $entry->range,
                $entry->framework,
                $theme->themeSlug,
                $theme->framework,
            ));
        }

        if (!$entry->admits($version)) {
            return new UiEntryVerdict($entry, UiVerdict::Refused, sprintf(
                "Module '%s' offers %s %s for %s; theme '%s' implements %s %s -- refused.",
                $entry->module,
                $entry->contract,
                $entry->range,
                $entry->framework,
                $theme->themeSlug,
                $entry->contract,
                $version,
            ));
        }

        return new UiEntryVerdict($entry, UiVerdict::Matched, sprintf(
            "Module '%s' offers %s %s for %s; theme '%s' implements %s %s -- matched.",
            $entry->module,
            $entry->contract,
            $entry->range,
            $entry->framework,
            $theme->themeSlug,
            $entry->contract,
            $version,
        ));
    }
}
