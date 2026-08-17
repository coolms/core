<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use DateTimeInterface;

interface ExpiresAtProviderInterface
{
    public DateTimeInterface $expiresAt {
        get;
        set;
    }
    public ?string $expiresAtAsString {
        get;
    }
    public bool $isExpired {
        get;
    }
}
