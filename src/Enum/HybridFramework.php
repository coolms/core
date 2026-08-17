<?php

declare(strict_types=1);

namespace CoolMS\Core\Enum;

enum HybridFramework: string
{
    case Next = 'next';
    case Nuxt = 'nuxt';
    case SvelteKit = 'sveltekit';
}
