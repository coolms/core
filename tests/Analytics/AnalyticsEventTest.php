<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Analytics;

use CoolMS\Core\Analytics\AnalyticsEvent;
use CoolMS\Core\Analytics\InvalidAnalyticsEventException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * The generic, privacy-safe analytics event VO (Core L0).
 */
final class AnalyticsEventTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function badTypes(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['PageView'];
        yield 'whitespace' => ['page view'];
        yield 'leading dot' => ['.pageview'];
        yield 'trailing dot' => ['pageview.'];
        yield 'slash' => ['page/view'];
        yield 'too long' => [str_repeat('a', AnalyticsEvent::MAX_TYPE_LENGTH + 1)];
    }

    #[Test]
    public function itPreservesEveryFieldOnConstruction(): void
    {
        $at = new DateTimeImmutable('2026-06-29 10:00:00');
        $event = new AnalyticsEvent(
            type: 'lead.submit',
            occurredAt: $at,
            path: '/contact',
            dimensions: ['country' => 'DE', 'device' => 'mobile'],
            value: 49.5,
            consent: ['necessary', 'analytics'],
            visitorRef: 'abc123',
            subjectRef: 'user-7',
        );

        self::assertSame('lead.submit', $event->type);
        self::assertSame($at, $event->occurredAt);
        self::assertSame('/contact', $event->path);
        self::assertSame(['country' => 'DE', 'device' => 'mobile'], $event->dimensions);
        self::assertSame(49.5, $event->value);
        self::assertSame(['necessary', 'analytics'], $event->consent);
        self::assertSame('abc123', $event->visitorRef);
        self::assertSame('user-7', $event->subjectRef);
    }

    #[Test]
    public function itDefaultsTheOptionalFields(): void
    {
        $event = new AnalyticsEvent('pageview', new DateTimeImmutable('2026-06-29 10:00:00'));

        self::assertNull($event->path);
        self::assertSame([], $event->dimensions);
        self::assertNull($event->value);
        self::assertSame([], $event->consent);
        self::assertNull($event->visitorRef);
        self::assertNull($event->subjectRef);
    }

    #[Test]
    #[DataProvider('badTypes')]
    public function itRejectsAMalformedType(string $type): void
    {
        $this->expectException(InvalidAnalyticsEventException::class);

        new AnalyticsEvent($type, new DateTimeImmutable('2026-06-29 10:00:00'));
    }

    #[Test]
    public function itAcceptsDottedAndHyphenatedTypes(): void
    {
        foreach (['pageview', 'lead.submit', 'search.zero_result', 'newsletter-confirm'] as $type) {
            $event = new AnalyticsEvent($type, new DateTimeImmutable('2026-06-29 10:00:00'));
            self::assertSame($type, $event->type);
        }
    }
}
