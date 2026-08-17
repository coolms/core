<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use CoolMS\Core\ValueObject\TimeOfDay;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CoolMS\Core\ValueObject\TimeOfDay
 */
final class TimeOfDayTest extends TestCase
{
    #[Test]
    public function constructorAcceptsValidHourMinuteSecond(): void
    {
        $t = new TimeOfDay(9, 30, 15);
        self::assertSame(9, $t->hour);
        self::assertSame(30, $t->minute);
        self::assertSame(15, $t->second);
    }

    #[Test]
    public function constructorRejectsHourBelowZero(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeOfDay(-1, 0);
    }

    #[Test]
    public function constructorRejectsHourAboveTwentyThree(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeOfDay(24, 0);
    }

    #[Test]
    public function constructorRejectsMinuteAboveFiftyNine(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeOfDay(12, 60);
    }

    #[Test]
    public function constructorRejectsSecondAboveFiftyNine(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeOfDay(12, 0, 60);
    }

    #[Test]
    public function fromStringParsesHHMM(): void
    {
        $t = TimeOfDay::fromString('09:30');
        self::assertSame(9, $t->hour);
        self::assertSame(30, $t->minute);
        self::assertSame(0, $t->second);
    }

    #[Test]
    public function fromStringParsesHHMMSS(): void
    {
        $t = TimeOfDay::fromString('09:30:15');
        self::assertSame(15, $t->second);
    }

    #[Test]
    public function fromStringRejectsGarbage(): void
    {
        $this->expectException(InvalidRangeException::class);
        TimeOfDay::fromString('not a time');
    }

    #[Test]
    public function fromStringRejectsOutOfRangeHour(): void
    {
        $this->expectException(InvalidRangeException::class);
        TimeOfDay::fromString('25:00');
    }

    #[Test]
    public function toStringOmitsSecondsWhenZero(): void
    {
        self::assertSame('09:30', new TimeOfDay(9, 30)->toString());
    }

    #[Test]
    public function toStringIncludesSecondsWhenNonZero(): void
    {
        self::assertSame('09:30:15', new TimeOfDay(9, 30, 15)->toString());
    }

    #[Test]
    public function compareToOrders(): void
    {
        $a = new TimeOfDay(9, 0);
        $b = new TimeOfDay(17, 0);
        self::assertLessThan(0, $a->compareTo($b));
        self::assertGreaterThan(0, $b->compareTo($a));
        self::assertSame(0, $a->compareTo(new TimeOfDay(9, 0)));
    }

    #[Test]
    public function totalSecondsReportsCorrectly(): void
    {
        self::assertSame(0, new TimeOfDay(0, 0)->totalSeconds());
        self::assertSame(3600, new TimeOfDay(1, 0)->totalSeconds());
        self::assertSame(86_399, new TimeOfDay(23, 59, 59)->totalSeconds());
    }
}
