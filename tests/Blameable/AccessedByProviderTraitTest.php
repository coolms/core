<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable;

use CoolMS\Core\Tests\Blameable\Fixture\AccessedByRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * `AccessedByProviderTrait` records which actor last read a record, as a raw
 * UUID with no foreign key behind it.
 *
 * The value moves with every read and may be cleared again, for a read made by
 * an anonymous visitor.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class AccessedByProviderTraitTest extends TestCase
{
    private const string ACTOR = '0199b3a4-6f1e-7c2a-9d3e-0a1b2c3d4e5f';

    private const string OTHER_ACTOR = '0199b3a4-6f1e-7c2a-9d3e-ffffffffffff';

    #[Test]
    public function aRecordMadeWithoutAnActorHasNone(): void
    {
        $record = new AccessedByRecord();

        self::assertNull($record->accessedBy);
        self::assertNull($record->accessedByAsString);
    }

    #[Test]
    public function anActorGivenAtConstructionIsKeptAndReadsAsItsCanonicalString(): void
    {
        $actor = Uuid::fromString(self::ACTOR);

        $record = new AccessedByRecord(accessedBy: $actor);

        self::assertSame($actor, $record->accessedBy);
        self::assertSame(self::ACTOR, $record->accessedByAsString);
    }

    #[Test]
    public function eachReadReplacesTheActorBeforeIt(): void
    {
        $record = new AccessedByRecord(accessedBy: Uuid::fromString(self::ACTOR));
        $next = Uuid::fromString(self::OTHER_ACTOR);

        $record->accessedBy = $next;

        self::assertSame($next, $record->accessedBy);
        self::assertSame(self::OTHER_ACTOR, $record->accessedByAsString);
    }

    #[Test]
    public function theActorCanBeClearedAgain(): void
    {
        $record = new AccessedByRecord(accessedBy: Uuid::fromString(self::ACTOR));

        $record->accessedBy = null;

        self::assertNull($record->accessedBy);
        self::assertNull($record->accessedByAsString);
    }
}
