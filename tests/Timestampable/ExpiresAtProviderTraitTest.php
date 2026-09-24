<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable;

use CoolMS\Core\Timestampable\ExpiresAtProviderInterface;
use CoolMS\Core\Timestampable\ExpiresAtProviderTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Whether an expiry has passed is decided by the clock a test sets, at an exact
 * instant, and through the trait itself rather than a copy of its rule.
 *
 * Midnight is where a wall read gives itself away: an expiry at 00:00 falls on
 * the next DAY, so a test that reads the wall passes or fails by the date it
 * happens to run on. The instants below straddle one midnight, a microsecond
 * either side. A trait that read the wall would contradict at least one of
 * them on ANY day: before that midnight it would call the last two unexpired,
 * after it the first one expired.
 */
final class ExpiresAtProviderTraitTest extends TestCase
{
    use ClockSensitiveTrait;

    private const string MIDNIGHT = '2026-09-25T00:00:00.000000+00:00';

    #[Test]
    public function aMicrosecondBeforeMidnightTheExpiryHasNotPassed(): void
    {
        self::mockTime(new DateTimeImmutable('2026-09-24T23:59:59.999999+00:00'));

        self::assertFalse($this->expiringAt(self::MIDNIGHT)->isExpired);
    }

    /**
     * The same object, asked again after the clock moves: `isExpired` is read
     * when it is asked, not fixed when the object was made.
     */
    #[Test]
    public function atMidnightExactlyTheSameExpiryHasPassed(): void
    {
        self::mockTime(new DateTimeImmutable('2026-09-24T23:59:59.999999+00:00'));
        $expiring = $this->expiringAt(self::MIDNIGHT);
        self::assertFalse($expiring->isExpired);

        self::mockTime(new DateTimeImmutable(self::MIDNIGHT));

        self::assertTrue($expiring->isExpired, 'an expiry has passed AT its instant, not only after it');
    }

    #[Test]
    public function aMicrosecondAfterMidnightItHasPassed(): void
    {
        self::mockTime(new DateTimeImmutable('2026-09-25T00:00:00.000001+00:00'));

        self::assertTrue($this->expiringAt(self::MIDNIGHT)->isExpired);
    }

    /**
     * Instants, not dates. At 02:59:59.999999 in Moscow the local date is
     * already the 25th, the expiry's own date, and the expiry has still not
     * passed, because that reading is 23:59:59.999999 in UTC. Written the other
     * way round, an expiry at 03:00 Moscow is the same UTC midnight.
     */
    #[Test]
    public function theZoneEitherSideIsWrittenInDecidesNothing(): void
    {
        self::mockTime(new DateTimeImmutable('2026-09-25T02:59:59.999999+03:00'));
        self::assertFalse($this->expiringAt(self::MIDNIGHT)->isExpired);
        self::assertFalse($this->expiringAt('2026-09-25T03:00:00.000000+03:00')->isExpired);

        self::mockTime(new DateTimeImmutable('2026-09-24T21:00:00.000000-03:00'));
        self::assertTrue($this->expiringAt('2026-09-25T03:00:00.000000+03:00')->isExpired);
    }

    /**
     * With no clock set the global clock is the native one, so an expiry an
     * hour gone has passed and one an hour away has not: what the trait
     * answered when it read the wall, it still answers.
     */
    #[Test]
    public function underTheNativeClockTheAnswerIsTheWalls(): void
    {
        Clock::set(new NativeClock());

        self::assertTrue($this->expiringAt(new DateTimeImmutable('-1 hour')->format('Y-m-d\TH:i:s.uP'))->isExpired);
        self::assertFalse($this->expiringAt(new DateTimeImmutable('+1 hour')->format('Y-m-d\TH:i:s.uP'))->isExpired);
    }

    private function expiringAt(string $instant): ExpiresAtProviderInterface
    {
        // The trait's own constructor, so the expiry is the trait's promoted property.
        return new class(new DateTimeImmutable($instant)) implements ExpiresAtProviderInterface {
            use ExpiresAtProviderTrait;
        };
    }
}
