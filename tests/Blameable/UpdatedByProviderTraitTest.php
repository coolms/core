<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable;

use CoolMS\Core\Tests\Blameable\Fixture\UpdatedByRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * `UpdatedByProviderTrait` records which actor last changed a record, as a raw
 * UUID with no foreign key behind it.
 *
 * Unlike the creator, this value is meant to move: every write replaces it, and
 * it may be cleared again, for a change made by no authenticated actor.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class UpdatedByProviderTraitTest extends TestCase
{
    private const string ACTOR = '0199b3a4-6f1e-7c2a-9d3e-0a1b2c3d4e5f';

    private const string OTHER_ACTOR = '0199b3a4-6f1e-7c2a-9d3e-ffffffffffff';

    #[Test]
    public function aRecordMadeWithoutAnActorHasNone(): void
    {
        $record = new UpdatedByRecord();

        self::assertNull($record->updatedBy);
        self::assertNull($record->updatedByAsString);
    }

    #[Test]
    public function anActorGivenAtConstructionIsKeptAndReadsAsItsCanonicalString(): void
    {
        $actor = Uuid::fromString(self::ACTOR);

        $record = new UpdatedByRecord(updatedBy: $actor);

        self::assertSame($actor, $record->updatedBy);
        self::assertSame(self::ACTOR, $record->updatedByAsString);
    }

    #[Test]
    public function eachWriteReplacesTheActorBeforeIt(): void
    {
        $record = new UpdatedByRecord(updatedBy: Uuid::fromString(self::ACTOR));
        $next = Uuid::fromString(self::OTHER_ACTOR);

        $record->updatedBy = $next;

        self::assertSame($next, $record->updatedBy);
        self::assertSame(self::OTHER_ACTOR, $record->updatedByAsString);
    }

    #[Test]
    public function theActorCanBeClearedAgain(): void
    {
        $record = new UpdatedByRecord(updatedBy: Uuid::fromString(self::ACTOR));

        $record->updatedBy = null;

        self::assertNull($record->updatedBy);
        self::assertNull($record->updatedByAsString);
    }
}
