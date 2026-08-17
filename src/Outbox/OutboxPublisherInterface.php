<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

/**
 * The relay's publish seam (Core L0) — hands a committed outbox message off to
 * its consumers. The default in-monolith impl dispatches an
 * {@see OutboxMessagePublished} event in-process; at service-extraction time it
 * swaps to a broker publisher WITHOUT touching the relay or producers.
 *
 * A throw signals a publish failure: the relay leaves the row unpublished and
 * bumps its attempt count for the next run.
 */
interface OutboxPublisherInterface
{
    public function publish(OutboxMessagePublished $message): void;
}
