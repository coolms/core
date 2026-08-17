<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Translation;

use CoolMS\Core\Translation\LocalizedExtras;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The write-side primitive for non-default-locale overrides under
 * `extras.i18n.{field}.{locale}` — shared by VFS Node title/description and
 * Media alt/caption. Round-trips with {@see LocalizedText::override()}.
 */
final class LocalizedExtrasTest extends TestCase
{
    #[Test]
    public function setsAnOverrideUnderTheI18nBranch(): void
    {
        $out = LocalizedExtras::applyOverride([], 'title', 'Заголовок', 'uk');

        self::assertSame(['i18n' => ['title' => ['uk' => 'Заголовок']]], $out);
    }

    #[Test]
    public function preservesSiblingExtrasFieldsAndLocales(): void
    {
        $extras = [
            'metaTitle' => 'SEO',
            'i18n' => ['title' => ['de' => 'Titel'], 'caption' => ['uk' => 'Підпис']],
        ];

        $out = LocalizedExtras::applyOverride($extras, 'title', 'Заголовок', 'uk');

        self::assertSame('SEO', $out['metaTitle']);
        self::assertSame('Titel', $out['i18n']['title']['de']);     // sibling locale kept
        self::assertSame('Заголовок', $out['i18n']['title']['uk']); // new override added
        self::assertSame('Підпис', $out['i18n']['caption']['uk']);  // sibling field kept
    }

    #[Test]
    public function clearingTheLastLocalePrunesTheFieldLeaf(): void
    {
        $extras = ['i18n' => ['title' => ['uk' => 'Заголовок']], 'status' => 'draft'];

        $out = LocalizedExtras::applyOverride($extras, 'title', null, 'uk');

        self::assertArrayNotHasKey('i18n', $out); // whole empty branch pruned
        self::assertSame('draft', $out['status']);
    }

    #[Test]
    public function clearingOneLocaleKeepsOtherLocalesAndFields(): void
    {
        $extras = ['i18n' => ['title' => ['uk' => 'A', 'de' => 'B'], 'caption' => ['uk' => 'C']]];

        $out = LocalizedExtras::applyOverride($extras, 'title', null, 'uk');

        self::assertArrayNotHasKey('uk', $out['i18n']['title']); // target removed
        self::assertSame('B', $out['i18n']['title']['de']);      // sibling locale kept
        self::assertSame('C', $out['i18n']['caption']['uk']);    // sibling field kept
    }

    #[Test]
    public function clearingAnAbsentLocaleIsANoOpThatStillPrunes(): void
    {
        // No 'uk' override to begin with; clearing it must not synthesise an empty leaf.
        $out = LocalizedExtras::applyOverride(['status' => 'draft'], 'title', null, 'uk');

        self::assertSame(['status' => 'draft'], $out);
    }

    #[Test]
    public function toleratesMalformedI18nBranch(): void
    {
        // A non-array i18n value (corrupt JSON) is treated as "no overrides".
        $out = LocalizedExtras::applyOverride(['i18n' => 'oops'], 'title', 'X', 'uk');

        self::assertSame(['i18n' => ['title' => ['uk' => 'X']]], $out);
    }
}
