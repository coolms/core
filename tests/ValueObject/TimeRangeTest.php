<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use CoolMS\Core\ValueObject\TimeOfDay;
use CoolMS\Core\ValueObject\TimeRange;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CoolMS\Core\ValueObject\TimeRange
 */
final class TimeRangeTest extends TestCase
{
    #[Test]
    public function constructorAcceptsForwardWindow(): void
    {
        $r = new TimeRange(new TimeOfDay(9, 0), new TimeOfDay(17, 0));
        self::assertSame(8 * 3600, $r->durationInSeconds());
    }

    #[Test]
    public function constructorRejectsEqualEndpoints(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeRange(new TimeOfDay(9, 0), new TimeOfDay(9, 0));
    }

    #[Test]
    public function constructorRejectsInvertedRange(): void
    {
        $this->expectException(InvalidRangeException::class);
        new TimeRange(new TimeOfDay(17, 0), new TimeOfDay(9, 0));
    }

    #[Test]
    public function containsHalfOpenSemantics(): void
    {
        $r = new TimeRange(new TimeOfDay(9, 0), new TimeOfDay(17, 0));
        self::assertTrue($r->contains(new TimeOfDay(9, 0)));
        self::assertTrue($r->contains(new TimeOfDay(12, 30)));
        self::assertFalse($r->contains(new TimeOfDay(17, 0)));
        self::assertFalse($r->contains(new TimeOfDay(8, 59)));
    }

    #[Test]
    public function overlapsTouchingBoundariesAreDisjoint(): void
    {
        $a = new TimeRange(new TimeOfDay(9, 0), new TimeOfDay(12, 0));
        $b = new TimeRange(new TimeOfDay(12, 0), new TimeOfDay(17, 0));
        self::assertFalse($a->overlaps($b));
    }

    #[Test]
    public function overlapsActualOverlap(): void
    {
        $a = new TimeRange(new TimeOfDay(9, 0), new TimeOfDay(13, 0));
        $b = new TimeRange(new TimeOfDay(12, 0), new TimeOfDay(17, 0));
        self::assertTrue($a->overlaps($b));
        self::assertTrue($b->overlaps($a));
    }

    #[Test]
    public function roundTripPreservesShape(): void
    {
        $original = new TimeRange(new TimeOfDay(9, 30), new TimeOfDay(17, 0));
        $restored = TimeRange::fromArray($original->toArray());
        self::assertEquals($original->toArray(), $restored->toArray());
    }

    #[Test]
    public function fromArrayRejectsNonStrings(): void
    {
        $this->expectException(InvalidRangeException::class);
        TimeRange::fromArray(['start' => 900, 'end' => '17:00']);
    }
}
