<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

/**
 * Mixin for aggregate roots that emit Domain Events.
 *
 * Usage:
 *   class MyEntity implements RecordedEventsProviderInterface
 *   {
 *       use RecordedEventsTrait;
 *
 *       public function doSomething(): void
 *       {
 * // ... mutate state ...
 *           $this->recordEvent(new SomethingHappened($this->id->toRfc4122(), ...));
 *       }
 *   }
 *
 * DomainEventPublisher calls flushRecordedEvents() once the persistence
 * adapter reports a completed flush, and dispatches each via Messenger.
 */
trait RecordedEventsTrait
{
    /**
     * @var DomainEventInterface[]
     */
    private array $recordedEvents = [];

    /**
     * Returns all recorded events and clears the list atomically.
     *
     * @return DomainEventInterface[]
     */
    public function flushRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    protected function recordEvent(DomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
