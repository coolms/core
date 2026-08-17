<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

final class PreSaveEvent extends AbstractLifecycleEvent implements EventInterface
{
    public private(set) EventName $eventName = EventName::PRE_SAVE;
}
