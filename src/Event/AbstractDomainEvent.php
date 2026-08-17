<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use DateTimeImmutable;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    /**
     * Each concrete event MUST define its own eventName via a property hook:
     *   public string $eventName { get => 'module.event_happened'; }
     */
    abstract public string $eventName { get; }

    /**
     * Default priority -- override in concrete event if needed:
     *   public EventPriority $priority { get => EventPriority::High; }
     */
    public EventPriority $priority { get => EventPriority::Normal; }

    public function __construct(
        public readonly string $entityId,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }
}
