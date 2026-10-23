<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable;

use CoolMS\Core\Exception\ImmutablePropertyException;
use CoolMS\Core\Tests\Blameable\Fixture\BlameableRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * `BlameableTrait` composes the creator, last-updater and last-reader traits
 * behind one constructor, so a record can call a single aliased constructor
 * from its own.
 *
 * Every actor defaults to null, for a listener to fill in from the security
 * context; a command or a seeder names the ones it already knows. Composed, each
 * field keeps the rule of the trait it came from.
 *
 * The fixture is the consumer's shape: a record whose constructor calls the
 * composite's under an alias, passing each actor by name.
 */
final class BlameableTraitTest extends TestCase
{
    private const string CREATOR = '0199b3a4-6f1e-7c2a-9d3e-000000000001';

    private const string UPDATER = '0199b3a4-6f1e-7c2a-9d3e-000000000002';

    private const string READER = '0199b3a4-6f1e-7c2a-9d3e-000000000003';

    #[Test]
    public function withNoActorGivenAllThreeAreLeftForAListenerToFill(): void
    {
        $record = new BlameableRecord();

        self::assertNull($record->createdBy);
        self::assertNull($record->updatedBy);
        self::assertNull($record->accessedBy);
        self::assertNull($record->createdByAsString);
        self::assertNull($record->updatedByAsString);
        self::assertNull($record->accessedByAsString);
    }

    /**
     * Three different actors, so an actor routed to the wrong field cannot pass
     * for the right one.
     */
    #[Test]
    public function eachActorGivenLandsInItsOwnField(): void
    {
        $creator = Uuid::fromString(self::CREATOR);
        $updater = Uuid::fromString(self::UPDATER);
        $reader = Uuid::fromString(self::READER);

        $record = new BlameableRecord(createdBy: $creator, updatedBy: $updater, accessedBy: $reader);

        self::assertSame($creator, $record->createdBy);
        self::assertSame($updater, $record->updatedBy);
        self::assertSame($reader, $record->accessedBy);
        self::assertSame(self::CREATOR, $record->createdByAsString);
        self::assertSame(self::UPDATER, $record->updatedByAsString);
        self::assertSame(self::READER, $record->accessedByAsString);
    }

    #[Test]
    public function aSeederNamingOnlyTheCreatorLeavesTheOtherTwoEmpty(): void
    {
        $creator = Uuid::fromString(self::CREATOR);

        $record = new BlameableRecord(createdBy: $creator);

        self::assertSame($creator, $record->createdBy);
        self::assertNull($record->updatedBy);
        self::assertNull($record->accessedBy);
    }

    #[Test]
    public function theCreatorStaysWriteOnceWhileTheOtherTwoFollowEveryWrite(): void
    {
        $creator = Uuid::fromString(self::CREATOR);
        $record = new BlameableRecord(createdBy: $creator);
        $updater = Uuid::fromString(self::UPDATER);
        $reader = Uuid::fromString(self::READER);

        $record->updatedBy = $updater;
        $record->accessedBy = $reader;
        try {
            $record->createdBy = $updater;
            self::fail('the creator was replaced');
        } catch (ImmutablePropertyException) {
        }

        self::assertSame($creator, $record->createdBy);
        self::assertSame($updater, $record->updatedBy);
        self::assertSame($reader, $record->accessedBy);
    }
}
