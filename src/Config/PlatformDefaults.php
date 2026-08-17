<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

/**
 * Platform-wide default user-facing settings. F6 Phase 1.
 *
 * The floor of the settings cascade `Platform -> User`: when a user has
 * no stored preference for one of these, this is what applies. The same
 * values feed the FE API manifest so anonymous / pre-login renders use
 * the deployment's real defaults instead of a hardcoded Western one.
 *
 * **Token shape mirrors the FE, deliberately.** `dateFormat` /
 * `timeFormat` / `weekStart` use the CLDR-ish tokens the Angular admin +
 * the existing profile contributors already speak (`yyyy-MM-dd`, `24h`,
 * `monday`), NOT PHP `date()` tokens (`Y-m-d`, `H:i`, `1`). That makes
 * this a drop-in base for the existing `array_merge($defaults, $stored)`
 * cascade with no translation layer. A backend formatter wanting PHP
 * tokens maps at its own call site.
 *
 * **Locale authority.** `$locale` is sourced from
 * `coolms_core.default_locale`, which `I18nBundle::prepend()` syncs from
 * the authoritative `coolms_i18n.default_locale` — the platform keeps
 * exactly one authoritative locale knob. It is the BCP-47 floor that
 * non-request contexts (exceptions, CLI, async handlers) translate
 * against when no request locale is in scope.
 *
 * Lives in `Core\Domain\Config` alongside {@see SupportedLocalesProvider}
 * — platform-wide, operator-configured, dependency-free. Excluded from
 * the autowire prototype scan; registered explicitly in `Core\…\Extension`.
 *
 * Extensible: number / currency / measurement-unit defaults land here
 * when a consumer needs them. Phase 1 keeps it to the five settings that
 * already have per-user counterparts.
 */
final readonly class PlatformDefaults
{
    public function __construct(
        public string $locale,
        public string $timezone,
        public string $dateFormat,
        public string $timeFormat,
        public string $weekStart,
        /**
         * Deployment brand accent for the admin, as `#rrggbb`, or null to keep
         * the stylesheet's own.
         *
         * Belongs here rather than in a parallel theming node because it is
         * literally what this class documents itself as: the floor of the
         * `Platform -> User` cascade. A user with no `accentColor` of their own
         * gets this; with no deployment value either, the palette's default
         * stands. Nullable for exactly that reason — an operator who has not
         * chosen a brand colour must not be given one, and a non-null default
         * here would freeze every deployment to today's amber.
         *
         * Trailing and defaulted so the five existing call sites, including the
         * tests that construct this with named arguments, are untouched.
         */
        public ?string $accentColor = null,
    ) {
    }

    /**
     * The calendar-section shape, matching `CalendarPreferencesContributor`'s
     * keys so Phase 2 can `array_merge($this->forCalendarSection(), $stored)`
     * as a literal drop-in for the contributor's hardcoded defaults.
     *
     * `defaultCalendarSlug` is intentionally absent: it has no platform
     * default (there is no platform-wide calendar), so it stays the
     * contributor's `null`.
     *
     * @return array{tz: string, dateFormat: string, timeFormat: string, weekStart: string}
     */
    public function forCalendarSection(): array
    {
        return [
            'tz' => $this->timezone,
            'dateFormat' => $this->dateFormat,
            'timeFormat' => $this->timeFormat,
            'weekStart' => $this->weekStart,
        ];
    }
}
