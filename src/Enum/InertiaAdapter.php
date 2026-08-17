<?php

declare(strict_types=1);

namespace CoolMS\Core\Enum;

enum InertiaAdapter: string
{
    case React = 'react';
    case Vue = 'vue';
    case Svelte = 'svelte';
}
