<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable;

use CoolMS\Core\Tests\Timestampable\Fixture\AccessedAtRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * `AccessedAtProviderTrait` stamps a record with the moment it was last read.
 * The stamp starts at the moment the record was made and moves with every read;
 * it is exposed as an ISO 8601 string for serialisation.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class AccessedAtProviderTraitTest extends TestCase
{
    #[Test]
    public function aNewRecordIsStampedWithTheMomentItWasMade(): void
    {
        $before = new DateTimeImmutable();
        $record = new AccessedAtRecord();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $record->accessedAt);
        self::assertLessThanOrEqual($after, $record->accessedAt);
    }

    #[Test]
    public function eachReadMovesTheStamp(): void
    {
        $record = new AccessedAtRecord();
        $later = new DateTimeImmutable('2999-01-01T00:00:00+00:00');

        $record->accessedAt = $later;

        self::assertSame($later, $record->accessedAt);
    }

    #[Test]
    public function theStringFormIsIso8601OfTheStampItself(): void
    {
        $record = new AccessedAtRecord();
        $record->accessedAt = new DateTimeImmutable('2026-09-25T03:00:00.250000+03:00');

        self::assertSame('2026-09-25T03:00:00+03:00', $record->accessedAtAsString);
    }

    /**
     * A persistence layer builds a record without calling its constructor and
     * sets the columns afterwards. Until it has, the string form answers null
     * rather than failing on an uninitialised property.
     */
    #[Test]
    public function aRecordBuiltWithoutItsConstructorHasNoStringFormYet(): void
    {
        $record = new ReflectionClass(AccessedAtRecord::class)->newInstanceWithoutConstructor();

        self::assertNull($record->accessedAtAsString);
    }
}
