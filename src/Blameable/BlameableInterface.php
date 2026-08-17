<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

/**
 * Composite blameable interface -- UUID-based, module-agnostic.
 *
 * Mirrors TimestampableInterface in structure but tracks actor UUIDs instead of timestamps.
 * Entities implementing this interface have their blameable fields populated automatically
 * by CoolMS\CoreBundle\Event\Behaviour\BlameableEntityEventListener on every
 * OnCreateEvent / OnUpdateEvent, using the current security context.
 */
interface BlameableInterface extends AccessedByProviderInterface, CreatedByProviderInterface, UpdatedByProviderInterface
{
}
