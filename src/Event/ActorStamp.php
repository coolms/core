<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries the authenticated user's identifier through the Messenger envelope.
 * Populated by DomainEventPublisher from the current Security context.
 */
final readonly class ActorStamp implements StampInterface
{
    public function __construct(
        public string $userId,
    ) {
    }
}
