<?php

declare(strict_types=1);

namespace CoolMS\Core\Space;

use CoolMS\Core\Settings\ModuleSettingsReaderInterface;
use Throwable;

/**
 * Which sites is a module turned on for?
 *
 * ⚠️ **Presence of a directory is a consequence, not a signal.** Documents listed
 * every site as a space, which assumes document handling belongs everywhere -- a
 * site running a blog will never use it and was offered it anyway. Inferring it
 * from whether `/docs/<site>` happens to exist is worse: a directory can be
 * created by hand, deleted, or exist with no permissions, and in all three cases
 * the UI is guessing. **An explicit flag is not guessable.**
 *
 * ## One shape, not five
 *
 * Media, forms and calendars face the same question. Solving it per module grows
 * five near-identical implementations that drift, so the query and the
 * Convention live here and only the module-specific part -- what to create when
 * a space is enabled -- is left to the module, via
 * {@see SpaceProvisionerInterface}.
 *
 * ## The convention
 *
 * A module declares a `siteScopable` settings block carrying {@see self::KEY},
 * defaulting to `false`. Per-site settings already exist, and there is exactly
 * one reader for them: it merges the declared defaults, then the platform row,
 * then the site's own. A module does not read the tiers itself.
 *
 * ⚠️ **Absent reads as OFF**, the same failure direction as the public widgets:
 * a settings tier that cannot be reached must never be the thing that turns a
 * feature on for a site that never asked for it.
 */
final readonly class ModuleSpaceSettings
{
    /** The one key this convention reserves inside a module's block. */
    public const string KEY = 'enabled';

    public function __construct(
        private ModuleSettingsReaderInterface $settings,
    ) {
    }

    /**
     * Is this module enabled for one site?
     *
     * @param string $settingsKey the module's block, e.g. `document.spaces`
     */
    public function isEnabledFor(string $settingsKey, string $siteSlug): bool
    {
        try {
            $effective = $this->settings->effective($settingsKey, $siteSlug);
        } catch (Throwable) {
            // ⚠️ An undeclared block, an unreachable tier, a typo in the key:
            // none of them are permission to show a space nobody enabled.
            return false;
        }

        $value = $effective[self::KEY] ?? false;

        return true === $value || 1 === $value || '1' === $value;
    }

    /**
     * The subset of `$siteSlugs` this module is enabled for, in the order given.
     *
     * ⚠️ Order preserved rather than sorted: the caller's order is the site
     * order a person already sees elsewhere, and re-sorting here would make two
     * lists of the same sites disagree for no reason.
     *
     * @param iterable<string> $siteSlugs
     *
     * @return list<string>
     */
    public function enabledFor(string $settingsKey, iterable $siteSlugs): array
    {
        $out = [];
        foreach ($siteSlugs as $slug) {
            if ($this->isEnabledFor($settingsKey, $slug)) {
                $out[] = $slug;
            }
        }

        return $out;
    }

    /**
     * Sites the module is not yet enabled for -- what an "Add space" action offers.
     *
     * ⚠️ The button is not "show me another one": it is "enable this module
     * here", and offering a site that already has it would make it read as the
     * former.
     *
     * @param iterable<string> $siteSlugs
     *
     * @return list<string>
     */
    public function availableFor(string $settingsKey, iterable $siteSlugs): array
    {
        $out = [];
        foreach ($siteSlugs as $slug) {
            if (!$this->isEnabledFor($settingsKey, $slug)) {
                $out[] = $slug;
            }
        }

        return $out;
    }
}
