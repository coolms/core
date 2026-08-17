<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

/**
 * Platform slug generator: turn a human title into a lowercase,
 * kebab-cased, URL- and filesystem-safe ASCII slug, honoring per-locale
 * NATIONAL transliteration rule sets — a Russian title slugs by the
 * Cyrillic→Latin scheme a Russian reader expects (`Юлия` → `yuliya`),
 * a German one expands umlauts (`Größe` → `groesse`) — rather than ICU's
 * generic Any-Latin fold (which would give `iuliia` / `grosse`).
 *
 * The ENGINE lives in Core; the DATA (national rule sets) is contributed
 * by I18n — or any module — via {@see TransliterationRuleSetInterface},
 * collected by tag. Callers that know their content locale (Article
 * naming per SiteSection, Page create) pass it; when omitted the platform
 * default locale ({@see \CoolMS\Core\Config\PlatformDefaults})
 * is the floor.
 *
 * A slug is a lossy, non-reversible projection — two distinct titles can
 * collapse to the same slug (`Café` and `Cafe`). Uniqueness is the
 * caller's concern (freeze-on-publish, per-folder collision suffixing);
 * this port only answers "what is the canonical slug for this text".
 */
interface SlugGeneratorInterface
{
    /**
     * @param string      $text   the human title, in any script
     * @param string|null $locale BCP-47 tag (`ru`, `de-CH`); null → platform default locale
     *
     * @return string lowercase kebab-case ASCII slug; '' when $text carries no sluggable content
     */
    public function slugify(string $text, ?string $locale = null): string;
}
