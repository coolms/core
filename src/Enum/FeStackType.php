<?php

declare(strict_types=1);

namespace CoolMS\Core\Enum;

enum FeStackType: string
{
    case SSR = 'ssr';
    case SPA = 'spa';
    case Inertia = 'inertia';
    case Hybrid = 'hybrid';
    case API = 'api';

    /**
     * @return array<string> all valid string values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
