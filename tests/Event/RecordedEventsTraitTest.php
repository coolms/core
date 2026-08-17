<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Event;

use CoolMS\Core\Event\DomainEventInterface;
use CoolMS\Core\Event\RecordedEventsProviderInterface;
use CoolMS\Core\Event\RecordedEventsTrait;
use PHPUnit\Framework\TestCase;

/** @internal */
interface RecordedEventsCarrierInterface extends RecordedEventsProviderInterface
{
    public function emit(DomainEventInterface $event): void;
}

final class RecordedEventsTraitTest extends TestCase
{
    public function testRecordEventAppendsToList(): void
    {
        $carrier = $this->makeCarrier();
        $event = $this->makeEvent();

        $carrier->emit($event);

        $events = $carrier->flushRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertSame($event, $events[0]);
    }

    public function testFlushRecordedEventsReturnsEvents(): void
    {
        $carrier = $this->makeCarrier();
        $e1 = $this->makeEvent();
        $e2 = $this->makeEvent();

        $carrier->emit($e1);
        $carrier->emit($e2);

        $this->assertSame([$e1, $e2], $carrier->flushRecordedEvents());
    }

    public function testFlushRecordedEventsClearsAfterFlush(): void
    {
        $carrier = $this->makeCarrier();
        $carrier->emit($this->makeEvent());
        $carrier->flushRecordedEvents();

        $this->assertSame([], $carrier->flushRecordedEvents());
    }

    public function testFlushRecordedEventsIsAtomicOnSecondCall(): void
    {
        $carrier = $this->makeCarrier();
        $carrier->emit($this->makeEvent());
        $carrier->flushRecordedEvents();

        $this->assertCount(0, $carrier->flushRecordedEvents());
    }

    public function testMultipleEventsPreserveOrder(): void
    {
        $carrier = $this->makeCarrier();
        $events = [];
        for ($i = 0; $i < 5; ++$i) {
            $events[] = $this->makeEvent();
            $carrier->emit($events[$i]);
        }

        $this->assertSame($events, $carrier->flushRecordedEvents());
    }

    private function makeCarrier(): RecordedEventsCarrierInterface
    {
        return new class implements RecordedEventsCarrierInterface {
            use RecordedEventsTrait;

            public function emit(DomainEventInterface $event): void
            {
                $this->recordEvent($event);
            }
        };
    }

    /** No expectations needed — stub is correct here, not a mock. */
    private function makeEvent(): DomainEventInterface
    {
        return $this->createStub(DomainEventInterface::class);
    }
}
