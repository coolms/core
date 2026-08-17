<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

interface RecordedEventsProviderInterface
{
    /**
     * Returns all recorded events and immediately clears the internal list.
     *
     * Atomic: it prevents duplicate dispatch if an exception occurs partway through
     * the handler loop -- events are gone from the entity as soon as this returns.
     *
     * @return DomainEventInterface[]
     */
    public function flushRecordedEvents(): array;
}
