<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\ChangeFeed;

use CoolMS\Core\ChangeFeed\SyncChange;
use CoolMS\Core\ChangeFeed\SyncChangeOp;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CoolMS\Core\ChangeFeed\SyncChange
 */
final class SyncChangeTest extends TestCase
{
    #[Test]
    public function upsertFactoryRecordsAnUpsertWithAMintedId(): void
    {
        $now = new DateTimeImmutable('2026-07-16T12:00:00+00:00');

        $change = SyncChange::upsert('coolms_calendar_items', 'row-uuid', $now);

        self::assertSame('coolms_calendar_items', $change->tableName);
        self::assertSame('row-uuid', $change->rowId);
        self::assertSame(SyncChangeOp::Upsert, $change->op);
        self::assertSame($now, $change->recordedAt);
        self::assertNotSame('', $change->id->toRfc4122()); // an id was minted by the identity trait
    }

    #[Test]
    public function deleteFactoryRecordsADelete(): void
    {
        $now = new DateTimeImmutable('2026-07-16T12:00:00+00:00');

        $change = SyncChange::delete('coolms_vfs_nodes', 'node-uuid', $now);

        self::assertSame(SyncChangeOp::Delete, $change->op);
        self::assertSame('coolms_vfs_nodes', $change->tableName);
        self::assertSame('node-uuid', $change->rowId);
    }
}
