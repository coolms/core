<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Outbox;

use CoolMS\Core\Outbox\OutboxMessage;
use CoolMS\Core\Outbox\OutboxRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * `OutboxRecord::fromMessage` — the message → row mapping: a fresh row is
 * unpublished with zero attempts, `createdAt` is the append time, and
 * `occurredAt` falls back to the append time when the producer omits it.
 */
final class OutboxRecordTest extends TestCase
{
    #[Test]
    public function itMapsAMessageToAnUnpublishedRow(): void
    {
        $occurredAt = new DateTimeImmutable('2026-06-20 09:00:00');
        $now = new DateTimeImmutable('2026-06-30 12:00:00');

        $record = OutboxRecord::fromMessage(new OutboxMessage('lead.converted', ['leadId' => 'abc'], 'evt-1', $occurredAt), $now);

        self::assertSame('lead.converted', $record->type);
        self::assertSame(['leadId' => 'abc'], $record->payload);
        self::assertSame('evt-1', $record->messageId);
        self::assertSame($occurredAt, $record->occurredAt);
        self::assertSame($now, $record->createdAt);
        self::assertNull($record->publishedAt, 'a fresh row is undelivered');
        self::assertSame(0, $record->attempts);
    }

    #[Test]
    public function occurredAtFallsBackToTheAppendTime(): void
    {
        $now = new DateTimeImmutable('2026-06-30 12:00:00');

        $record = OutboxRecord::fromMessage(new OutboxMessage('lead.converted'), $now);

        self::assertSame($now, $record->occurredAt);
        self::assertSame($now, $record->createdAt);
    }
}
