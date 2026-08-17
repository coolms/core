<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Carries a unique v7 UUID trace ID for distributed tracing / log correlation.
 * Generated per-flush by DomainEventPublisher.
 */
final readonly class TraceIdStamp implements StampInterface
{
    public function __construct(
        public string $traceId,
    ) {
    }
}
