<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use DateTimeInterface;

interface CreatedAtProviderInterface
{
    public DateTimeInterface $createdAt {
        get;
        set;
    }
    public ?string $createdAtAsString {
        get;
    }
}
