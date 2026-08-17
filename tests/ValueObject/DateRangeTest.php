<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use CoolMS\Core\ValueObject\DateRange;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CoolMS\Core\ValueObject\DateRange
 */
final class DateRangeTest extends TestCase
{
    #[Test]
    public function constructorNormalisesToMidnightUtc(): void
    {
        $start = new DateTimeImmutable('2026-05-01T14:30:00+05:00');
        $end = new DateTimeImmutable('2026-05-31T03:00:00-08:00');

        $range = new DateRange($start, $end);

        self::assertSame('2026-05-01', $range->start->format('Y-m-d'));
        self::assertSame('00:00:00', $range->start->format('H:i:s'));
        self::assertSame('+00:00', $range->start->format('P'));
        self::assertSame('2026-05-31', $range->end->format('Y-m-d'));
    }

    #[Test]
    public function constructorAcceptsEqualStartAndEndWhenInclusive(): void
    {
        $d = new DateTimeImmutable('2026-05-01');
        $range = new DateRange($d, $d, includesEnd: true);
        self::assertSame(1, $range->durationInDays());
    }

    #[Test]
    public function constructorRejectsInvertedRangeInclusive(): void
    {
        $this->expectException(InvalidRangeException::class);
        new DateRange(
            new DateTimeImmutable('2026-05-31'),
            new DateTimeImmutable('2026-05-01'),
        );
    }

    #[Test]
    public function constructorRejectsEqualEndpointsWhenHalfOpen(): void
    {
        $this->expectException(InvalidRangeException::class);
        new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-01'),
            includesEnd: false,
        );
    }

    #[Test]
    public function containsHonoursClosedSemantics(): void
    {
        $range = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-31'),
        );

        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-01')));
        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-15')));
        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-31')));
        self::assertFalse($range->contains(new DateTimeImmutable('2026-04-30')));
        self::assertFalse($range->contains(new DateTimeImmutable('2026-06-01')));
    }

    #[Test]
    public function containsHonoursHalfOpenSemantics(): void
    {
        $range = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-31'),
            includesEnd: false,
        );

        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-30')));
        self::assertFalse($range->contains(new DateTimeImmutable('2026-05-31')));
    }

    #[Test]
    public function durationInDaysCountsInclusively(): void
    {
        $range = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-03'),
        );
        self::assertSame(3, $range->durationInDays());
    }

    #[Test]
    public function durationInDaysHalfOpenSubtractsEnd(): void
    {
        $range = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-03'),
            includesEnd: false,
        );
        self::assertSame(2, $range->durationInDays());
    }

    #[Test]
    public function overlapsDetectsContiguousClosedRanges(): void
    {
        $a = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-15'),
        );
        $b = new DateRange(
            new DateTimeImmutable('2026-05-15'),
            new DateTimeImmutable('2026-05-31'),
        );

        // Touching endpoints share one day on closed semantics.
        self::assertTrue($a->overlaps($b));
        self::assertTrue($b->overlaps($a));
    }

    #[Test]
    public function overlapsRejectsDisjointRanges(): void
    {
        $a = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-10'),
        );
        $b = new DateRange(
            new DateTimeImmutable('2026-06-01'),
            new DateTimeImmutable('2026-06-10'),
        );

        self::assertFalse($a->overlaps($b));
    }

    #[Test]
    public function roundTripPreservesShape(): void
    {
        $original = new DateRange(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-31'),
            includesEnd: false,
        );

        $restored = DateRange::fromArray($original->toArray());

        self::assertEquals($original->toArray(), $restored->toArray());
        self::assertSame(false, $restored->includesEnd);
    }

    #[Test]
    public function fromArrayDefaultsIncludesEndToTrue(): void
    {
        $r = DateRange::fromArray(['start' => '2026-05-01', 'end' => '2026-05-31']);
        self::assertTrue($r->includesEnd);
    }

    #[Test]
    public function fromArrayRejectsNonStringEndpoints(): void
    {
        $this->expectException(InvalidRangeException::class);
        DateRange::fromArray(['start' => 12345, 'end' => '2026-05-31']);
    }

    #[Test]
    public function fromArrayRejectsMalformedDates(): void
    {
        $this->expectException(InvalidRangeException::class);
        DateRange::fromArray(['start' => 'not-a-date', 'end' => '2026-05-31']);
    }
}
