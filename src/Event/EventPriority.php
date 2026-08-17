<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

enum EventPriority: string
{
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    public function sortWeight(): int
    {
        return match ($this) {
            self::High => 0,
            self::Normal => 1,
            self::Low => 2,
        };
    }
}
