<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Health;

use CoolMS\Core\Health\DependencyState;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function array_map;
use function array_values;
use function count;

/**
 * The four states a dependency can be in, and the one number that should be zero.
 *
 * The number counts exactly what was asked and stayed silent. An `absent`
 * dependency was never asked; an `unknown` one was asked and the answer could
 * not tell alive from dead. Neither may hold the number above zero, or an idle
 * installation reads as broken and the number stops meaning anything -- while
 * an `ok` claimed for an empty queue is the trap the fourth state exists to
 * refuse.
 */
final class DependencyStateTest extends TestCase
{
    #[Test]
    public function answeredIsOkAndNotFailing(): void
    {
        $state = DependencyState::answered('Database', 'SELECT 1', 'answered');

        self::assertSame('ok', $state->status());
        self::assertTrue($state->configured);
        self::assertTrue($state->answered);
        self::assertFalse($state->inconclusive);
        self::assertFalse($state->isFailing());
    }

    #[Test]
    public function silentIsDownAndCountsWhenRequired(): void
    {
        $state = DependencyState::silent('Centrifugo', 'POST /api publish', 'connection refused');

        self::assertSame('DOWN', $state->status());
        self::assertTrue($state->configured);
        self::assertFalse($state->answered);
        self::assertTrue($state->isFailing());
    }

    #[Test]
    public function silentOptionalIsDownButDoesNotCount(): void
    {
        $state = DependencyState::silent(
            'Mail intake',
            'mailboxes imported',
            'none inside the window',
            required: false,
        );

        self::assertSame('DOWN', $state->status());
        self::assertFalse($state->isFailing());
    }

    #[Test]
    public function notConfiguredIsAbsentAndNeverCounts(): void
    {
        $state = DependencyState::notConfigured('Search index', 'GET /health', 'no host configured', required: true);

        self::assertSame('absent', $state->status());
        self::assertFalse($state->configured);
        self::assertFalse($state->answered);
        self::assertFalse($state->isFailing());
    }

    #[Test]
    public function inconclusiveIsUnknownNotOkAndDoesNotCount(): void
    {
        $lastSeen = new DateTimeImmutable('2026-09-21 04:25:30');
        $state = DependencyState::inconclusive(
            'Outbox relay',
            'rows unpublished for more than 600s',
            'cannot tell -- no unpublished row to watch the relay through',
            lastActivityAt: $lastSeen,
        );

        self::assertSame('unknown', $state->status());
        self::assertTrue($state->configured, 'it was asked, so it is configured');
        self::assertFalse($state->answered, 'an answer that cannot decide is not an answer');
        self::assertTrue($state->inconclusive);
        self::assertFalse($state->isFailing(), 'an idle system must not hold the number above zero');
        self::assertSame($lastSeen, $state->lastActivityAt);
    }

    #[Test]
    public function inconclusiveIsNotOkEvenWhenRequired(): void
    {
        $state = DependencyState::inconclusive(
            'Worker',
            'a heartbeat handled within 300s',
            'no dispatcher running',
            required: true,
        );

        self::assertNotSame('ok', $state->status());
        self::assertSame('unknown', $state->status());
    }

    #[Test]
    public function theNumberThatShouldBeZeroCountsOnlyTheSilentRequired(): void
    {
        $states = [
            DependencyState::answered('a', 'q', 'yes'),
            DependencyState::silent('b', 'q', 'nothing'),
            DependencyState::silent('c', 'q', 'nothing', required: false),
            DependencyState::notConfigured('d', 'q', 'not declared', required: true),
            DependencyState::inconclusive('e', 'q', 'cannot tell'),
        ];

        $failing = array_values(array_filter($states, static fn (DependencyState $s): bool => $s->isFailing()));

        self::assertCount(1, $failing);
        self::assertSame(['b'], array_map(static fn (DependencyState $s): string => $s->name, $failing));
        self::assertSame(5, count($states), 'five asked, one counted');
    }
}
