<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

/**
 * What a message the platform sends as itself is for. It decides the order held mail goes out in after a stop: a
 * one-time code is wanted now, transactional mail (a confirmation link, a reset, an invitation) soon, the rest when it
 * comes. Independent of who the message is addressed to or how it is delivered.
 */
enum SystemMailPurpose: string
{
    case OneTimeCode = 'one-time-code';

    case Transactional = 'transactional';

    case Other = 'other';

    /** Its place in the release after a stop: lower goes first. */
    public function rank(): int
    {
        return match ($this) {
            self::OneTimeCode => 0,
            self::Transactional => 1,
            self::Other => 2,
        };
    }
}
