<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

final class PostSaveEvent extends AbstractLifecycleEvent implements EventInterface
{
    public private(set) EventName $eventName = EventName::POST_SAVE;
}
