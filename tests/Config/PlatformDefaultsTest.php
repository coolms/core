<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Config;

use CoolMS\Core\Config\PlatformDefaults;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * F6 Phase 1 -- the platform-defaults value object.
 *
 * It's a dumb readonly VO; the only behaviour worth pinning is
 * `forCalendarSection()`, which maps the VO onto the exact key shape
 * `CalendarPreferencesContributor` uses so Phase 2 can drop it in as the
 * cascade floor.
 */
final class PlatformDefaultsTest extends TestCase
{
    #[Test]
    public function exposesAllFiveSettings(): void
    {
        $defaults = new PlatformDefaults(
            locale: 'uk',
            timezone: 'Europe/Kyiv',
            dateFormat: 'dd/MM/yyyy',
            timeFormat: '24h',
            weekStart: 'monday',
        );

        self::assertSame('uk', $defaults->locale);
        self::assertSame('Europe/Kyiv', $defaults->timezone);
        self::assertSame('dd/MM/yyyy', $defaults->dateFormat);
        self::assertSame('24h', $defaults->timeFormat);
        self::assertSame('monday', $defaults->weekStart);
    }

    #[Test]
    public function forCalendarSectionMapsOntoContributorKeyShape(): void
    {
        $defaults = new PlatformDefaults(
            locale: 'en',
            timezone: 'America/New_York',
            dateFormat: 'MM/dd/yyyy',
            timeFormat: '12h',
            weekStart: 'sunday',
        );

        // Note: `timezone` surfaces under the contributor's `tz` key.
        // `defaultCalendarSlug` is intentionally absent (no platform
        // default for it -- stays the contributor's null).
        self::assertSame(
            [
                'tz' => 'America/New_York',
                'dateFormat' => 'MM/dd/yyyy',
                'timeFormat' => '12h',
                'weekStart' => 'sunday',
            ],
            $defaults->forCalendarSection(),
        );
    }
}
