<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Event;

use CoolMS\Core\Event\AbstractDomainEvent;
use CoolMS\Core\Event\EventPriority;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AbstractDomainEventTest extends TestCase
{
    public function testEntityIdIsStored(): void
    {
        $event = $this->makeStub('abc-123');
        $this->assertSame('abc-123', $event->entityId);
    }

    public function testOccurredAtIsSetOnCreation(): void
    {
        $event = $this->makeStub('id-1');
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredAt);
    }

    public function testDefaultPriorityIsNormal(): void
    {
        $event = $this->makeStub('id-1');
        $this->assertSame(EventPriority::Normal, $event->priority);
    }

    public function testEventNameIsAbstract(): void
    {
        $event = $this->makeStub('id-1');
        $this->assertSame('test.happened', $event->eventName);
    }

    public function testPriorityCanBeOverridden(): void
    {
        $event = $this->makeHighPriorityStub('id-2');
        $this->assertSame(EventPriority::High, $event->priority);
    }

    private function makeStub(string $entityId, ?DateTimeImmutable $at = null): AbstractDomainEvent
    {
        return new class($entityId, $at ?? new DateTimeImmutable()) extends AbstractDomainEvent {
            public string $eventName { get => 'test.happened'; }
        };
    }

    private function makeHighPriorityStub(string $entityId): AbstractDomainEvent
    {
        return new class($entityId) extends AbstractDomainEvent {
            public string $eventName { get => 'test.high'; }
            public EventPriority $priority { get => EventPriority::High; }
        };
    }
}
