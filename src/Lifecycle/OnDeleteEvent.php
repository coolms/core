<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

final class OnDeleteEvent extends AbstractLifecycleEvent implements EventInterface
{
    public private(set) EventName $eventName = EventName::ON_DELETE;
}
