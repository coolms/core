<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The mark on a message the platform sends as itself, as message headers: that it is the platform's own, what it is
 * for, and -- for one carrying a code or a link -- when it stops meaning anything, so a copy held while it cannot be
 * sent is dropped then rather than sent late. Any module adds these headers to a message it builds; the mailer side
 * reads them, and takes every one of them off before the message leaves. Mail with a From of its own is never marked.
 *
 * Plain names and values, so a module marks a message without depending on a mail library.
 */
final class SystemMailMark
{
    public const string HEADER = 'X-CoolMS-Mail';

    public const string VALUE = 'system';

    public const string PURPOSE_HEADER = 'X-CoolMS-Mail-Purpose';

    public const string EXPIRES_HEADER = 'X-CoolMS-Mail-Expires';

    /** Every header of the mark, in the order headers() gives them: the ones taken off before a message leaves. */
    public const array NAMES = [self::HEADER, self::PURPOSE_HEADER, self::EXPIRES_HEADER];

    private function __construct()
    {
    }

    /**
     * The headers to add to a message the platform sends as itself.
     *
     * @return array<string, string>
     */
    public static function headers(SystemMailPurpose $purpose, ?DateTimeImmutable $meansNothingAfter = null): array
    {
        $headers = [self::HEADER => self::VALUE, self::PURPOSE_HEADER => $purpose->value];
        if (null !== $meansNothingAfter) {
            $headers[self::EXPIRES_HEADER] = $meansNothingAfter->format(DateTimeInterface::ATOM);
        }

        return $headers;
    }

    /** Whether a message's `X-CoolMS-Mail` value marks it as the platform's own. */
    public static function isMarked(?string $value): bool
    {
        return self::VALUE === $value;
    }

    /** What a message is for, from its `X-CoolMS-Mail-Purpose` value; unsaid or unknown is other. */
    public static function purposeFrom(?string $value): SystemMailPurpose
    {
        return SystemMailPurpose::tryFrom((string) $value) ?? SystemMailPurpose::Other;
    }

    /** When a message stops meaning anything, from its `X-CoolMS-Mail-Expires` value; null if unsaid or unreadable. */
    public static function expiresFrom(?string $value): ?DateTimeImmutable
    {
        $at = null === $value ? false : DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $value);

        return false === $at ? null : $at;
    }
}
