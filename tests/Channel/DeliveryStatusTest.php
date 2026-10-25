<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Channel;

use CoolMS\Core\Channel\DeliveryStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** The status rules every direct channel and every consumer reads the same way. */
final class DeliveryStatusTest extends TestCase
{
    #[Test]
    public function progressOnlyMovesForward(): void
    {
        self::assertSame(DeliveryStatus::Delivered, DeliveryStatus::Accepted->then(DeliveryStatus::Delivered));
        self::assertSame(DeliveryStatus::Read, DeliveryStatus::Delivered->then(DeliveryStatus::Read));
        self::assertSame(DeliveryStatus::Delivered, DeliveryStatus::Delivered->then(DeliveryStatus::Accepted));
        self::assertSame(DeliveryStatus::Read, DeliveryStatus::Read->then(DeliveryStatus::Queued));
    }

    #[Test]
    public function aFailureEndsAnAttemptOnlyBeforeDelivery(): void
    {
        self::assertSame(DeliveryStatus::Failed, DeliveryStatus::Accepted->then(DeliveryStatus::Failed));
        self::assertSame(DeliveryStatus::Expired, DeliveryStatus::Queued->then(DeliveryStatus::Expired));
        self::assertSame(DeliveryStatus::Delivered, DeliveryStatus::Delivered->then(DeliveryStatus::Failed));
        self::assertSame(DeliveryStatus::Failed, DeliveryStatus::Failed->then(DeliveryStatus::Delivered));
        self::assertTrue(DeliveryStatus::Expired->isTerminalFailure());
        self::assertFalse(DeliveryStatus::Delivered->isTerminalFailure());
    }

    #[Test]
    public function unknownMovesNothingAndReachesNothing(): void
    {
        self::assertSame(DeliveryStatus::Accepted, DeliveryStatus::Accepted->then(DeliveryStatus::Unknown));
        self::assertFalse(DeliveryStatus::Unknown->reaches(DeliveryStatus::Queued));
        self::assertNull(DeliveryStatus::Unknown->progress());
    }

    #[Test]
    public function reachesComparesProgress(): void
    {
        self::assertTrue(DeliveryStatus::Read->reaches(DeliveryStatus::Delivered));
        self::assertTrue(DeliveryStatus::Delivered->reaches(DeliveryStatus::Delivered));
        self::assertFalse(DeliveryStatus::Accepted->reaches(DeliveryStatus::Delivered));
        self::assertFalse(DeliveryStatus::Failed->reaches(DeliveryStatus::Accepted));
    }
}
