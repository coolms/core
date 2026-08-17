<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use DateTimeImmutable;

interface DomainEventInterface
{
    public string $entityId {
        get;
    }
    public DateTimeImmutable $occurredAt {
        get;
    }
    public string $eventName {
        get;
    }
    public EventPriority $priority {
        get;
    }
}
