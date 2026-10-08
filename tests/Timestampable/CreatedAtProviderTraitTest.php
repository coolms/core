<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable;

use CoolMS\Core\Exception\ImmutablePropertyException;
use CoolMS\Core\Tests\Timestampable\Fixture\CreatedAtRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * `CreatedAtProviderTrait` stamps a record with the moment it was made, or with
 * an instant the caller already knows (an import, a migration), and exposes the
 * stamp as an ISO 8601 string for serialisation.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class CreatedAtProviderTraitTest extends TestCase
{
    #[Test]
    public function aNewRecordIsStampedWithTheMomentItWasMade(): void
    {
        $before = new DateTimeImmutable();
        $record = new CreatedAtRecord();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $record->createdAt);
        self::assertLessThanOrEqual($after, $record->createdAt);
    }

    #[Test]
    public function anInstantGivenAtConstructionIsKeptAsGiven(): void
    {
        $known = new DateTimeImmutable('2020-01-02T03:04:05+00:00');

        $record = new CreatedAtRecord(createdAt: $known);

        self::assertSame($known, $record->createdAt);
    }

    #[Test]
    public function onceSetTheMomentCannotBeReplaced(): void
    {
        $known = new DateTimeImmutable('2020-01-02T03:04:05+00:00');
        $record = new CreatedAtRecord(createdAt: $known);

        try {
            $record->createdAt = new DateTimeImmutable('2021-01-01T00:00:00+00:00');
            self::fail('a second moment was accepted');
        } catch (ImmutablePropertyException $e) {
            self::assertSame(
                CreatedAtRecord::class . '::createdAt cannot be set after initialization',
                $e->getMessage(),
            );
        }

        self::assertSame($known, $record->createdAt, 'the refused write must leave the first moment in place');
    }

    /**
     * What a persistence layer does: it builds the record without its
     * constructor and sets the column once.
     */
    #[Test]
    public function aRecordBuiltWithoutItsConstructorTakesItsMomentOnce(): void
    {
        $record = new ReflectionClass(CreatedAtRecord::class)->newInstanceWithoutConstructor();
        $known = new DateTimeImmutable('2020-01-02T03:04:05+00:00');

        $record->createdAt = $known;

        self::assertSame($known, $record->createdAt);
        $this->expectException(ImmutablePropertyException::class);
        $record->createdAt = new DateTimeImmutable();
    }

    /**
     * To the second, in the zone the instant was written in: the fraction is
     * dropped and the offset is not converted.
     */
    #[Test]
    public function theStringFormIsIso8601InTheInstantsOwnZone(): void
    {
        $record = new CreatedAtRecord(createdAt: new DateTimeImmutable('2026-09-25T03:00:00.250000+03:00'));

        self::assertSame('2026-09-25T03:00:00+03:00', $record->createdAtAsString);
    }

    /**
     * A persistence layer builds a record without calling its constructor and
     * sets the columns afterwards. Until it has, the string form answers null
     * rather than failing on an uninitialised property.
     */
    #[Test]
    public function aRecordBuiltWithoutItsConstructorHasNoStringFormYet(): void
    {
        $record = new ReflectionClass(CreatedAtRecord::class)->newInstanceWithoutConstructor();

        self::assertNull($record->createdAtAsString);
    }
}
