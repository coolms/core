<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Lifecycle;

use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Lifecycle\EventName;
use CoolMS\Core\Lifecycle\OnCreateEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class OnCreateEventTest extends TestCase
{
    private IdentifierProviderInterface $entity;

    public function testEventNameIsOnCreate(): void
    {
        $event = new OnCreateEvent($this->entity);
        $this->assertSame(EventName::ON_CREATE, $event->eventName);
    }

    public function testEventNameValueIsEntityOnCreate(): void
    {
        $event = new OnCreateEvent($this->entity);
        $this->assertSame('entity.onCreate', $event->eventName->value);
    }

    protected function setUp(): void
    {
        $this->entity = new class implements IdentifierProviderInterface {
            public Uuid $id {
                get => Uuid::v7();
            }
        };
    }
}
