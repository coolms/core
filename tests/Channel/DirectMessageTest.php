<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Channel;

use CoolMS\Core\Channel\DeliveryReceipt;
use CoolMS\Core\Channel\DeliveryReceiptReported;
use CoolMS\Core\Channel\DeliveryStatus;
use CoolMS\Core\Channel\DirectMessage;
use CoolMS\Core\Channel\MessagePurpose;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * One message to one person, as a sender hands it to a direct channel, and the later status a channel reports for
 * it. The sender's key is what ties the two together, so it is required and the receipt carries it back.
 */
final class DirectMessageTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, string}> */
    public static function blanks(): iterable
    {
        yield 'no sender' => ['', 'k', '+1', 'text'];
        yield 'no key' => ['reach', ' ', '+1', 'text'];
        yield 'no address' => ['reach', 'k', '', 'text'];
        yield 'no text' => ['reach', 'k', '+1', ''];
    }

    #[Test]
    public function aMessageCarriesWhatTheChannelAndConsentNeed(): void
    {
        $message = new DirectMessage('reach', 'run-1/step-2', '+375291234567', 'Your order is ready', MessagePurpose::Transactional);

        self::assertSame('reach', $message->sender);
        self::assertSame('run-1/step-2', $message->key);
        self::assertSame('+375291234567', $message->address);
        self::assertSame(MessagePurpose::Transactional, $message->purpose);
    }

    #[Test]
    #[DataProvider('blanks')]
    public function aBlankPartIsRefused(string $sender, string $key, string $address, string $text): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DirectMessage($sender, $key, $address, $text, MessagePurpose::Otp);
    }

    #[Test]
    public function purposesAreTheOnesASenderDeclares(): void
    {
        self::assertSame(['otp', 'transactional', 'marketing'], array_map(
            static fn (MessagePurpose $p): string => $p->value,
            MessagePurpose::cases(),
        ));
        self::assertTrue(MessagePurpose::Marketing->needsMarketingConsent());
        self::assertFalse(MessagePurpose::Otp->needsMarketingConsent());
        self::assertFalse(MessagePurpose::Transactional->needsMarketingConsent());
    }

    #[Test]
    public function aLaterStatusComesBackAsAnEventKeyedBySenderAndKey(): void
    {
        $at = new DateTimeImmutable('2026-10-25T21:00:00+00:00');
        $receipt = new DeliveryReceipt('sms', 'reach', 'run-1/step-2', DeliveryStatus::Delivered, 'DELIVRD err:000', 'abc123', $at);
        $event = new DeliveryReceiptReported($receipt);

        self::assertSame('channel.delivery_receipt_reported', $event->eventName);
        self::assertSame('reach:run-1/step-2', $event->entityId, 'one id per attempt across senders');
        self::assertSame($at, $event->occurredAt);
        self::assertSame($receipt, $event->receipt);
        self::assertTrue($event->isFor('reach'));
        self::assertFalse($event->isFor('identity'));
    }

    #[Test]
    public function aReceiptNeedsItsSenderAndKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeliveryReceipt('sms', 'reach', '', DeliveryStatus::Delivered, 'x', null, new DateTimeImmutable());
    }
}
