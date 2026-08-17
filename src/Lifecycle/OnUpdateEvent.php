<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

final class OnUpdateEvent extends AbstractLifecycleEvent implements EventInterface
{
    public private(set) EventName $eventName = EventName::ON_UPDATE;
}
