<?php

declare(strict_types=1);

namespace CoolMS\Core\Enum;

enum SpaFramework: string
{
    case Angular = 'angular';
    case React = 'react';
    case Vue = 'vue';
    case Svelte = 'svelte';
}
