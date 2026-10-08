<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable;

use CoolMS\Core\Tests\Timestampable\Fixture\TimestampedRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * `TimestampableTrait` composes the created, updated and accessed timestamp
 * traits behind one constructor, so a record can call a single aliased
 * constructor from its own and have all three stamps taken at once.
 *
 * The fixture is the consumer's shape: a record whose constructor calls the
 * composite's under an alias.
 */
final class TimestampableTraitTest extends TestCase
{
    #[Test]
    public function allThreeStampsAreTakenWhenTheRecordIsMade(): void
    {
        $before = new DateTimeImmutable();
        $record = new TimestampedRecord();
        $after = new DateTimeImmutable();

        $stamps = [
            'createdAt' => $record->createdAt,
            'updatedAt' => $record->updatedAt,
            'accessedAt' => $record->accessedAt,
        ];
        foreach ($stamps as $name => $stamp) {
            self::assertGreaterThanOrEqual($before, $stamp, $name);
            self::assertLessThanOrEqual($after, $stamp, $name);
        }
    }

    #[Test]
    public function movingOneStampLeavesTheOtherTwoWhereTheyWere(): void
    {
        $record = new TimestampedRecord();
        $createdAt = $record->createdAt;
        $accessedAt = $record->accessedAt;

        $record->updatedAt = new DateTimeImmutable('2999-01-01T00:00:00+00:00');

        self::assertSame($createdAt, $record->createdAt);
        self::assertSame($accessedAt, $record->accessedAt);
    }

    /**
     * Three different instants, so a string form reading the wrong stamp
     * cannot pass for the right one.
     */
    #[Test]
    public function eachStringFormFollowsItsOwnStamp(): void
    {
        $record = new TimestampedRecord();
        $record->createdAt = new DateTimeImmutable('2026-01-01T00:00:00+00:00');
        $record->updatedAt = new DateTimeImmutable('2026-02-02T00:00:00+00:00');
        $record->accessedAt = new DateTimeImmutable('2026-03-03T00:00:00+00:00');

        self::assertSame('2026-01-01T00:00:00+00:00', $record->createdAtAsString);
        self::assertSame('2026-02-02T00:00:00+00:00', $record->updatedAtAsString);
        self::assertSame('2026-03-03T00:00:00+00:00', $record->accessedAtAsString);
    }

    #[Test]
    public function aRecordBuiltWithoutItsConstructorHasNoStringFormsYet(): void
    {
        $record = new ReflectionClass(TimestampedRecord::class)->newInstanceWithoutConstructor();

        self::assertNull($record->createdAtAsString);
        self::assertNull($record->updatedAtAsString);
        self::assertNull($record->accessedAtAsString);
    }
}
