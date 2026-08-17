<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use DateTimeInterface;

interface AccessedAtProviderInterface
{
    public DateTimeInterface $accessedAt {
        get;
        set;
    }
    public ?string $accessedAtAsString {
        get;
    }
}
