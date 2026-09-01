<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Theme;

use CoolMS\Core\Theme\ThemeAssets;
use CoolMS\Core\Theme\ThemeAuthoringAssets;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * ThemeAuthoringAssets repeats ThemeAssets' css/js entry shape on purpose, and
 * the two are free to drift the moment someone edits one of them. A docblock
 * asking a human to keep two types in sync is not a guarantee, so the shared
 * half is pinned here instead.
 *
 * What is deliberately NOT shared -- the resolved slug, and the chain-merged
 * ordering -- is asserted too, so a future "these look the same, collapse them"
 * has to argue with a failing test rather than with a comment.
 */
final class ThemeAssetsShapeTest extends TestCase
{
    public function testCssAndJsEntryShapesAreIdentical(): void
    {
        self::assertSame(
            $this->documentedEntryTypes(ThemeAssets::class),
            $this->documentedEntryTypes(ThemeAuthoringAssets::class),
            'ThemeAssets and ThemeAuthoringAssets must describe css/js entries '
            . 'identically. An entry that grows `media` or `integrity` has to '
            . 'grow in both, or the SSR context and the authoring surface stop '
            . 'agreeing on what a stylesheet record is.',
        );
    }

    public function testBothCarryTheSameRecordsUnchanged(): void
    {
        $css = [['url' => '/themes/x/app.css'], ['url' => '/themes/x/extra.css']];
        $js = [['url' => '/themes/x/app.js']];

        self::assertSame($css, (new ThemeAssets($css, $js))->css);
        self::assertSame($js, (new ThemeAssets($css, $js))->js);

        self::assertSame($css, (new ThemeAuthoringAssets('x', $css, $js))->css);
        self::assertSame($js, (new ThemeAuthoringAssets('x', $css, $js))->js);
    }

    public function testTheDivergenceIsIntentionalAndStillThere(): void
    {
        self::assertTrue(
            (new ReflectionClass(ThemeAuthoringAssets::class))->hasProperty('themeSlug'),
            'ThemeAuthoringAssets carries the resolved slug -- that is one of the '
            . 'two reasons it is not just ThemeAssets.',
        );
        self::assertFalse(
            (new ReflectionClass(ThemeAssets::class))->hasProperty('themeSlug'),
            'ThemeAssets is one theme\'s own assets and has no slug of its own. '
            . 'If it grew one, the two types would have converged and the '
            . 'duplication should be revisited rather than maintained.',
        );
    }

    public function testEmptyConstructorsAgreeOnTheEmptyShape(): void
    {
        self::assertSame([], ThemeAssets::empty()->css);
        self::assertSame([], ThemeAssets::empty()->js);
        self::assertSame([], ThemeAuthoringAssets::none()->css);
        self::assertSame([], ThemeAuthoringAssets::none()->js);
        self::assertNull(ThemeAuthoringAssets::none()->themeSlug);
    }

    /**
     * The `@param` types the constructor documents for $css and $js.
     *
     * @param class-string $class
     *
     * @return array{css: string, js: string}
     */
    private function documentedEntryTypes(string $class): array
    {
        $ctor = (new ReflectionClass($class))->getConstructor();
        self::assertInstanceOf(ReflectionMethod::class, $ctor);

        $doc = $ctor->getDocComment();
        self::assertIsString($doc, $class . ' must document its css/js entry shape.');

        $found = [];
        foreach (['css', 'js'] as $name) {
            // the documented type contains spaces -- `list<array{url: string}>`
            // -- so match lazily up to the variable rather than to whitespace
            $matched = preg_match(
                '/@param\s+(.+?)\s+\$' . $name . '\b/',
                $doc,
                $m,
            );
            self::assertSame(1, $matched, $class . ' does not document $' . $name . '.');
            $found[$name] = $m[1];
        }

        return $found;
    }
}
