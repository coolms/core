<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable;

use CoolMS\Core\Tests\Timestampable\Fixture\UpdatedAtRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * `UpdatedAtProviderTrait` stamps a record with the moment it last changed. The
 * stamp starts at the moment the record was made and moves with every write; it
 * is exposed as an ISO 8601 string for serialisation.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias. It uses this trait alone, without a creation
 * time, which is a shape a consumer is free to choose.
 */
final class UpdatedAtProviderTraitTest extends TestCase
{
    #[Test]
    public function aNewRecordIsStampedWithTheMomentItWasMade(): void
    {
        $before = new DateTimeImmutable();
        $record = new UpdatedAtRecord();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $record->updatedAt);
        self::assertLessThanOrEqual($after, $record->updatedAt);
    }

    #[Test]
    public function eachWriteMovesTheStamp(): void
    {
        $record = new UpdatedAtRecord();
        $later = new DateTimeImmutable('2999-01-01T00:00:00+00:00');

        $record->updatedAt = $later;

        self::assertSame($later, $record->updatedAt);
    }

    /**
     * The string form reads `updatedAt` and nothing else. This record declares
     * no creation time, so a string form that consulted one would answer null
     * here while `updatedAt` holds a value.
     */
    #[Test]
    public function theStringFormIsIso8601OfTheStampItself(): void
    {
        $record = new UpdatedAtRecord();
        $record->updatedAt = new DateTimeImmutable('2026-09-25T03:00:00.250000+03:00');

        self::assertSame('2026-09-25T03:00:00+03:00', $record->updatedAtAsString);
    }

    /**
     * A persistence layer builds a record without calling its constructor and
     * sets the columns afterwards. Until it has, the string form answers null
     * rather than failing on an uninitialised property.
     */
    #[Test]
    public function aRecordBuiltWithoutItsConstructorHasNoStringFormYet(): void
    {
        $record = new ReflectionClass(UpdatedAtRecord::class)->newInstanceWithoutConstructor();

        self::assertNull($record->updatedAtAsString);
    }
}
