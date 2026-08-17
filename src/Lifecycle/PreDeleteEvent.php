<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

final class PreDeleteEvent extends AbstractLifecycleEvent implements EventInterface
{
    public private(set) EventName $eventName = EventName::PRE_DELETE;
}
