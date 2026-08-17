<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

/**
 * Write-time companion to {@see LocalizedText} for the per-instance
 * "canonical source + non-default overrides" model.
 *
 * The default-locale value lives in a canonical slot owned by the caller (a
 * real column for Node title/description, a plain `extras` key for Media
 * alt/caption); every NON-default locale lives under
 * `extras.i18n.{field}.{locale}`. {@see LocalizedText::override()} reads that
 * shape back; this class writes the override branch and prunes it so a cleared
 * value never leaves a dangling `{}` leaf behind.
 *
 * Pure + stateless: it only reshapes the `extras` array, returning a new one.
 * The default-locale (canonical) write is intentionally NOT handled here —
 * that slot differs per call site (a setter vs an extras key), so the caller
 * branches on `$locale === $defaultLocale` and only delegates the override
 * branch to {@see applyOverride()}.
 */
final class LocalizedExtras
{
    /**
     * Set or clear a non-default-locale override under `extras.i18n.{field}.{locale}`,
     * pruning empty `{field}` and `i18n` leaves on removal.
     *
     * A `null` value removes the override for `$locale` (reverting reads to the
     * canonical source); any other string sets it. Sibling fields, sibling
     * locales, and non-i18n extras keys are preserved untouched.
     *
     * @param array<string, mixed> $extras
     *
     * @return array<string, mixed>
     */
    public static function applyOverride(array $extras, string $field, ?string $value, string $locale): array
    {
        $i18n = is_array($extras['i18n'] ?? null) ? $extras['i18n'] : [];
        $fieldMap = is_array($i18n[$field] ?? null) ? $i18n[$field] : [];

        if (null === $value) {
            unset($fieldMap[$locale]);
        } else {
            $fieldMap[$locale] = $value;
        }

        if ([] === $fieldMap) {
            unset($i18n[$field]);
        } else {
            $i18n[$field] = $fieldMap;
        }

        if ([] === $i18n) {
            unset($extras['i18n']);
        } else {
            $extras['i18n'] = $i18n;
        }

        return $extras;
    }
}
