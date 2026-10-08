<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable;

use CoolMS\Core\Exception\ImmutablePropertyException;
use CoolMS\Core\Tests\Blameable\Fixture\CreatedByRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * `CreatedByProviderTrait` records which actor created a record, as a raw UUID
 * with no foreign key behind it.
 *
 * The value is write-once. A record made without an actor holds null until
 * something fills it in -- typically a listener reading the security context --
 * and from then on it can be neither replaced nor cleared.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class CreatedByProviderTraitTest extends TestCase
{
    private const string ACTOR = '0199b3a4-6f1e-7c2a-9d3e-0a1b2c3d4e5f';

    private const string OTHER_ACTOR = '0199b3a4-6f1e-7c2a-9d3e-ffffffffffff';

    #[Test]
    public function aRecordMadeWithoutAnActorHasNone(): void
    {
        $record = new CreatedByRecord();

        self::assertNull($record->createdBy);
        self::assertNull($record->createdByAsString);
    }

    #[Test]
    public function anActorGivenAtConstructionIsKeptAndReadsAsItsCanonicalString(): void
    {
        $actor = Uuid::fromString(self::ACTOR);

        $record = new CreatedByRecord(createdBy: $actor);

        self::assertSame($actor, $record->createdBy);
        self::assertSame(self::ACTOR, $record->createdByAsString);
    }

    #[Test]
    public function anActorStillMissingMayBeFilledInLater(): void
    {
        $record = new CreatedByRecord();
        $actor = Uuid::fromString(self::ACTOR);

        $record->createdBy = $actor;

        self::assertSame($actor, $record->createdBy);
        self::assertSame(self::ACTOR, $record->createdByAsString);
    }

    #[Test]
    public function onceSetTheActorCannotBeReplaced(): void
    {
        $actor = Uuid::fromString(self::ACTOR);
        $record = new CreatedByRecord(createdBy: $actor);

        try {
            $record->createdBy = Uuid::fromString(self::OTHER_ACTOR);
            self::fail('a second actor was accepted');
        } catch (ImmutablePropertyException $e) {
            self::assertSame(
                CreatedByRecord::class . '::createdBy cannot be set after initialization',
                $e->getMessage(),
            );
        }

        self::assertSame($actor, $record->createdBy, 'the refused write must leave the first actor in place');
    }

    /**
     * Filled in after construction rather than given to it, so this also shows
     * the guard does not depend on how the first actor arrived.
     */
    #[Test]
    public function onceSetTheActorCannotBeCleared(): void
    {
        $record = new CreatedByRecord();
        $actor = Uuid::fromString(self::ACTOR);
        $record->createdBy = $actor;

        try {
            $record->createdBy = null;
            self::fail('the actor was cleared');
        } catch (ImmutablePropertyException) {
        }

        self::assertSame($actor, $record->createdBy);
        self::assertSame(self::ACTOR, $record->createdByAsString);
    }
}
