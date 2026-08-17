<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use CoolMS\Core\ValueObject\DateTimeRange;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CoolMS\Core\ValueObject\DateTimeRange
 */
final class DateTimeRangeTest extends TestCase
{
    #[Test]
    public function constructorPreservesTimezone(): void
    {
        $start = new DateTimeImmutable('2026-05-01T09:00:00+05:30');
        $end = new DateTimeImmutable('2026-05-01T17:00:00+05:30');

        $range = new DateTimeRange($start, $end);

        self::assertSame('+05:30', $range->start->format('P'));
        self::assertSame('+05:30', $range->end->format('P'));
    }

    #[Test]
    public function constructorRejectsInvertedRangeHalfOpen(): void
    {
        $this->expectException(InvalidRangeException::class);
        new DateTimeRange(
            new DateTimeImmutable('2026-05-01T17:00:00Z'),
            new DateTimeImmutable('2026-05-01T17:00:00Z'),
            includesEnd: false,
        );
    }

    #[Test]
    public function containsIsZoneSafe(): void
    {
        $range = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T09:00:00+00:00'),
            new DateTimeImmutable('2026-05-01T17:00:00+00:00'),
        );

        // 14:30 +05:30 == 09:00 UTC -> inside
        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-01T14:30:00+05:30')));
        // 03:00 -08:00 == 11:00 UTC -> inside
        self::assertTrue($range->contains(new DateTimeImmutable('2026-05-01T03:00:00-08:00')));
        // 18:01 +00:00 -> outside
        self::assertFalse($range->contains(new DateTimeImmutable('2026-05-01T18:01:00+00:00')));
    }

    #[Test]
    public function durationInSecondsCorrect(): void
    {
        $range = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T09:00:00Z'),
            new DateTimeImmutable('2026-05-01T17:00:00Z'),
        );
        self::assertSame(8 * 3600, $range->durationInSeconds());
    }

    #[Test]
    public function overlapsTouchingBoundaryWhenInclusive(): void
    {
        $a = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T09:00:00Z'),
            new DateTimeImmutable('2026-05-01T12:00:00Z'),
        );
        $b = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T12:00:00Z'),
            new DateTimeImmutable('2026-05-01T17:00:00Z'),
        );
        self::assertTrue($a->overlaps($b));
    }

    #[Test]
    public function overlapsTouchingBoundaryDisjointWhenHalfOpen(): void
    {
        $a = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T09:00:00Z'),
            new DateTimeImmutable('2026-05-01T12:00:00Z'),
            includesEnd: false,
        );
        $b = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T12:00:00Z'),
            new DateTimeImmutable('2026-05-01T17:00:00Z'),
            includesEnd: false,
        );
        self::assertFalse($a->overlaps($b));
    }

    #[Test]
    public function roundTripPreservesShape(): void
    {
        $original = new DateTimeRange(
            new DateTimeImmutable('2026-05-01T09:00:00+02:00'),
            new DateTimeImmutable('2026-05-01T17:00:00+02:00'),
        );

        $restored = DateTimeRange::fromArray($original->toArray());

        self::assertSame(
            $original->start->getTimestamp(),
            $restored->start->getTimestamp(),
        );
        self::assertSame(
            $original->end->getTimestamp(),
            $restored->end->getTimestamp(),
        );
        self::assertSame('+02:00', $restored->start->format('P'));
    }

    #[Test]
    public function fromArrayRejectsBadIso(): void
    {
        $this->expectException(InvalidRangeException::class);
        DateTimeRange::fromArray(['start' => 'not-iso', 'end' => '2026-05-01T17:00:00Z']);
    }
}
