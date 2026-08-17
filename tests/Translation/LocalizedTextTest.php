<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Translation;

use CoolMS\Core\Translation\LocalizedText;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The read-time projection for a flat localized-text map (the value produced by
 * the `localizedText` form type / `LocalizedTextType` and stored as JSON).
 */
final class LocalizedTextTest extends TestCase
{
    #[Test]
    public function picksTheRequestedLocale(): void
    {
        self::assertSame('Каталог', LocalizedText::pick(['en' => 'Catalog', 'uk' => 'Каталог'], 'uk'));
    }

    #[Test]
    public function fallsBackToTheFallbackLocale(): void
    {
        self::assertSame('Catalog', LocalizedText::pick(['en' => 'Catalog'], 'uk', 'en'));
    }

    #[Test]
    public function fallsBackToFirstNonEmptyWhenNeitherLocalePresent(): void
    {
        self::assertSame('De', LocalizedText::pick(['de' => 'De'], 'uk', 'en'));
    }

    #[Test]
    public function skipsEmptyStringSlots(): void
    {
        self::assertSame('Каталог', LocalizedText::pick(['en' => '', 'uk' => 'Каталог'], 'en', 'en'));
    }

    #[Test]
    public function returnsAPlainStringVerbatim(): void
    {
        self::assertSame('legacy', LocalizedText::pick('legacy', 'uk'));
    }

    #[Test]
    public function yieldsEmptyForMalformedOrAllEmptyInput(): void
    {
        self::assertSame('', LocalizedText::pick(null, 'uk'));
        self::assertSame('', LocalizedText::pick(42, 'uk'));
        self::assertSame('', LocalizedText::pick(['en' => ''], 'en'));
    }

    // ── override(): exact-locale override else the source fallback ──────────────

    #[Test]
    public function overrideReturnsTheExactLocaleOverride(): void
    {
        self::assertSame('Вступне відео', LocalizedText::override(['uk' => 'Вступне відео'], 'uk', 'Intro video'));
    }

    #[Test]
    public function overrideFallsBackToTheSourceWhenLocaleAbsent(): void
    {
        // No `uk` override → the source column, NOT another locale's override
        // (the key difference from pick()).
        self::assertSame('Intro video', LocalizedText::override(['de' => 'Einführungsvideo'], 'uk', 'Intro video'));
    }

    #[Test]
    public function overrideFallsBackToTheSourceForNoOverridesOrEmptyOverride(): void
    {
        self::assertSame('Intro video', LocalizedText::override(null, 'uk', 'Intro video'));
        self::assertSame('Intro video', LocalizedText::override([], 'uk', 'Intro video'));
        self::assertSame('Intro video', LocalizedText::override(['uk' => ''], 'uk', 'Intro video'));
    }

    #[Test]
    public function overridePropagatesANullSourceWhenNoOverride(): void
    {
        // The Node column can be null (no title set) — that flows through.
        self::assertNull(LocalizedText::override(['de' => 'X'], 'uk', null));
    }
}
