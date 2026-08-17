<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

/**
 * Read-time helpers for a localized text value -- the FLAT `array<locale, string>`
 * map produced by the `localizedText` form type and stored as JSON (dynamic-entity
 * `extras`, Node `extras`, etc.).
 *
 * This is the per-instance CONTENT counterpart to the catalogue/XLIFF model
 * ({@see LabelResolverInterface}): where the catalogue holds one translation
 * shared by every instance of a schema, a localized-text map holds this row's
 * own value per locale. Both ultimately answer "what string for which locale?",
 * but the storage differs on purpose: a catalogue entry is schema-level and
 * ships with the code, while this map is row data and belongs with the row.
 *
 * Pure + stateless: no service, no I18n dependency. Callers pass the locale
 * they want (typically the resolved request locale) and the source-locale
 * fallback. Tolerant of malformed input (non-string entries, the value already
 * being a plain string) so it is safe to call on raw JSON read back from a
 * column.
 */
final class LocalizedText
{
    /**
     * Resolve a localized-text map to a single string for `$locale`, falling
     * back to `$fallbackLocale`, then to the first non-empty entry, then ''.
     *
     * @param mixed $value expected `array<string, string>`; a plain string is
     *                     returned as-is (un-localized legacy value), anything
     *                     else yields ''
     */
    public static function pick(mixed $value, ?string $locale, string $fallbackLocale = 'en'): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (!is_array($value)) {
            return '';
        }

        foreach ([$locale, $fallbackLocale] as $candidate) {
            if (null !== $candidate && isset($value[$candidate]) && is_string($value[$candidate]) && '' !== $value[$candidate]) {
                return $value[$candidate];
            }
        }

        // Last resort: first non-empty string anywhere in the map.
        foreach ($value as $entry) {
            if (is_string($entry) && '' !== $entry) {
                return $entry;
            }
        }

        return '';
    }

    /**
     * Resolve an OVERRIDE map against a source `$fallback`: returns the non-empty
     * override stored for the EXACT `$locale`, otherwise `$fallback`. Unlike
     * {@see pick()} it does NOT cross-fall-back to another locale's override — a
     * missing translation yields the source, never a different language.
     *
     * This is the per-instance "canonical column + non-default overrides" model:
     * the source column (`$fallback`) holds the default-locale value, while
     * `$overrides` (e.g. `extras.i18n.title`) holds only the non-default locales.
     * So a default-locale request (no override key) returns the column, and a
     * non-default request returns its override or falls back to the column.
     *
     * @param mixed $overrides expected `array<string, string>`; anything else
     *                         means "no overrides" and yields `$fallback`
     */
    public static function override(mixed $overrides, string $locale, ?string $fallback): ?string
    {
        if (is_array($overrides)) {
            $value = $overrides[$locale] ?? null;
            if (is_string($value) && '' !== $value) {
                return $value;
            }
        }

        return $fallback;
    }
}
