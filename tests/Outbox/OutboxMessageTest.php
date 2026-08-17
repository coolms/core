<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Outbox;

use CoolMS\Core\Outbox\InvalidOutboxMessageException;
use CoolMS\Core\Outbox\OutboxMessage;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The outbox message VO: a typed, immutable event body with a
 * guarded non-empty type and friendly defaults.
 */
final class OutboxMessageTest extends TestCase
{
    #[Test]
    public function itCarriesTheProducerSuppliedFields(): void
    {
        $at = new DateTimeImmutable('2026-06-20 09:00:00');
        $message = new OutboxMessage('lead.converted', ['leadId' => 'abc'], 'evt-1', $at);

        self::assertSame('lead.converted', $message->type);
        self::assertSame(['leadId' => 'abc'], $message->payload);
        self::assertSame('evt-1', $message->messageId);
        self::assertSame($at, $message->occurredAt);
    }

    #[Test]
    public function itDefaultsTheOptionalFields(): void
    {
        $message = new OutboxMessage('lead.converted');

        self::assertSame([], $message->payload);
        self::assertNull($message->messageId);
        self::assertNull($message->occurredAt);
    }

    #[Test]
    public function itRejectsAnEmptyType(): void
    {
        $this->expectException(InvalidOutboxMessageException::class);

        new OutboxMessage('');
    }

    #[Test]
    public function itRejectsAWhitespaceOnlyType(): void
    {
        $this->expectException(InvalidOutboxMessageException::class);

        new OutboxMessage('   ');
    }
}
