<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use DateTimeInterface;

interface UpdatedAtProviderInterface
{
    public DateTimeInterface $updatedAt {
        get;
        set;
    }
    public ?string $updatedAtAsString {
        get;
    }
}
