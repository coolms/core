<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries the event priority so transport middleware can route accordingly.
 * High -- sync (in-process); Low -- async (Redis/RabbitMQ).
 */
final readonly class PriorityStamp implements StampInterface
{
    public function __construct(
        public EventPriority $priority,
    ) {
    }
}
