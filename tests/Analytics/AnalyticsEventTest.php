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
        self::assertNull($event->recognitionRef);
        self::assertNull($event->consentRecordId);
    }

    #[Test]
    public function requestContextFillsOnlyWhatTheProducerLeftAbsent(): void
    {
        $event = new AnalyticsEvent(
            'lead.submit',
            new DateTimeImmutable('2026-06-29 10:00:00'),
            dimensions: ['form' => 'contact'],
            consent: ['necessary'],
            subjectRef: 'user-7',
        );

        $enriched = $event->withRequestContext('day-ref', ['device' => 'mobile', 'form' => 'ambient'], ['necessary', 'analytics'], 'rid-1', 'rec-1');

        self::assertSame('day-ref', $enriched->visitorRef);
        self::assertSame('rid-1', $enriched->recognitionRef);
        self::assertSame('rec-1', $enriched->consentRecordId);
        self::assertSame('user-7', $enriched->subjectRef, 'the producer\'s subject is kept');
        self::assertSame(['necessary'], $enriched->consent, 'a declared legal basis is never re-labelled');
        self::assertSame(['device' => 'mobile', 'form' => 'contact'], $enriched->dimensions, 'domain dimensions win');

        $again = $enriched->withRequestContext('other-ref', [], [], 'rid-2', 'rec-2');
        self::assertSame('day-ref', $again->visitorRef, 'a present ref is not overwritten');
        self::assertSame('rid-1', $again->recognitionRef);
        self::assertSame('rec-1', $again->consentRecordId);
    }

    #[Test]
    public function withOnlyReferencesKeepsExactlyTheNamedOnes(): void
    {
        $event = new AnalyticsEvent(
            'pageview',
            new DateTimeImmutable('2026-06-29 10:00:00'),
            consent: ['necessary', 'analytics'],
            visitorRef: 'day-ref',
            subjectRef: 'user-7',
            recognitionRef: 'rid-1',
            consentRecordId: 'rec-1',
        );

        $measured = $event->withOnlyReferences(['visitorRef']);
        self::assertSame('day-ref', $measured->visitorRef);
        self::assertNull($measured->subjectRef);
        self::assertNull($measured->recognitionRef);
        self::assertSame('rec-1', $measured->consentRecordId, 'the decision a row rests on is not a reference to the visitor');

        $nobody = $event->withOnlyReferences([]);
        self::assertNull($nobody->visitorRef);
        self::assertNull($nobody->subjectRef);
        self::assertNull($nobody->recognitionRef);
        self::assertSame(['necessary', 'analytics'], $nobody->consent);
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
