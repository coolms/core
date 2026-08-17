<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

/**
 * A national transliteration rule set contributed to
 * {@see SlugGeneratorInterface}. The slug ENGINE is a Core concern; WHICH
 * Latin letters a given script maps to is a locale/i18n concern, so the
 * concrete rule sets live in the I18n module (or any module that wants to
 * ship one) and fan into the slugger by tag — no compiler pass, the
 * service glob + {@see self::TAG} do the wiring.
 *
 * A rule set's {@see map()} is applied to the LOWERCASED raw title BEFORE
 * the generic ICU ASCII fold, so a national letter reaches its expected
 * form (`ä` → `ae`, `щ` → `shch`) instead of ICU's lossy default
 * (`ä` → `a`, `щ` → the ICU Cyrillic scheme). Because the slugger
 * lowercases first, a rule set supplies LOWERCASE keys only.
 *
 * One rule set per BCP-47 base locale is the norm; if two are registered
 * for the same locale the slugger merges them, first-registered winning
 * per character (registration order = tag order, deterministic).
 */
interface TransliterationRuleSetInterface
{
    public const string TAG = 'coolms.slug.transliteration_rule_set';

    /**
     * The BCP-47 base locale this rule set applies to — `ru`, `de`, … .
     * A region/script subtag is ignored by the slugger (it matches on the
     * base), so return the base form.
     */
    public function locale(): string;

    /**
     * Lowercase grapheme → ASCII replacement, applied to the lowercased
     * raw title before the generic ASCII fold. Replacements are ASCII and
     * may be multi-character (`ж` → `zh`) or empty (`ь` → '', the Russian
     * soft sign is dropped). Keys are lowercase — the slugger lowercases
     * the title first, so uppercase keys would never match.
     *
     * @return array<string, string>
     */
    public function map(): array;
}
