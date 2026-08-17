<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

interface EventInterface
{
    public EventName $eventName { get; }
}
