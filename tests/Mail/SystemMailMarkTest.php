<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Mail;

use CoolMS\Core\Mail\SystemMailMark;
use CoolMS\Core\Mail\SystemMailPurpose;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * A message the platform sends as itself is marked so, with what it is for and, where it carries a code or a link,
 * when it stops meaning anything. The mark is headers any module can add to a message it builds and the mailer side
 * reads back; it is taken off before the message leaves.
 */
final class SystemMailMarkTest extends TestCase
{
    #[Test]
    public function aMarkIsTheSystemHeaderWithThePurposeAndTheExpiryWhenGiven(): void
    {
        $expires = new DateTimeImmutable('2026-10-27T12:10:00+00:00');

        self::assertSame([
            'X-CoolMS-Mail' => 'system',
            'X-CoolMS-Mail-Purpose' => 'one-time-code',
            'X-CoolMS-Mail-Expires' => '2026-10-27T12:10:00+00:00',
        ], SystemMailMark::headers(SystemMailPurpose::OneTimeCode, $expires));
        self::assertSame(
            ['X-CoolMS-Mail' => 'system', 'X-CoolMS-Mail-Purpose' => 'other'],
            SystemMailMark::headers(SystemMailPurpose::Other),
        );
    }

    #[Test]
    public function whatAMarkSaysIsReadBackAndAnythingElseIsNoMark(): void
    {
        $headers = SystemMailMark::headers(SystemMailPurpose::Transactional, new DateTimeImmutable('2026-10-27T13:00:00Z'));

        self::assertTrue(SystemMailMark::isMarked($headers[SystemMailMark::HEADER]));
        self::assertFalse(SystemMailMark::isMarked(null));
        self::assertFalse(SystemMailMark::isMarked('System'));
        self::assertSame(SystemMailPurpose::Transactional, SystemMailMark::purposeFrom($headers[SystemMailMark::PURPOSE_HEADER]));
        self::assertSame(SystemMailPurpose::Other, SystemMailMark::purposeFrom(null), 'unsaid is other');
        self::assertSame(SystemMailPurpose::Other, SystemMailMark::purposeFrom('urgent'), 'unknown is other');
        self::assertEquals(
            new DateTimeImmutable('2026-10-27T13:00:00Z'),
            SystemMailMark::expiresFrom($headers[SystemMailMark::EXPIRES_HEADER]),
        );
        self::assertNull(SystemMailMark::expiresFrom(null));
        self::assertNull(SystemMailMark::expiresFrom('tomorrow'), 'an expiry that does not read is none');
    }

    #[Test]
    public function everyHeaderOfTheMarkIsNamedSoNoneLeaves(): void
    {
        $all = SystemMailMark::headers(SystemMailPurpose::OneTimeCode, new DateTimeImmutable('2026-10-27T13:00:00Z'));

        self::assertSame(array_keys($all), SystemMailMark::NAMES);
    }

    #[Test]
    public function aOneTimeCodeGoesFirstAfterAStopThenTransactionalMailThenTheRest(): void
    {
        self::assertLessThan(SystemMailPurpose::Transactional->rank(), SystemMailPurpose::OneTimeCode->rank());
        self::assertLessThan(SystemMailPurpose::Other->rank(), SystemMailPurpose::Transactional->rank());
    }
}
