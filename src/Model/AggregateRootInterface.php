<?php

declare(strict_types=1);

namespace CoolMS\Core\Model;

/**
 * Canonical DDD marker for aggregate roots.
 *
 * All aggregate roots in the platform implement this interface.
 * No methods required -- it is a pure marker used by:
 *   - Domain Explorer (entity classification)
 *   - Future: aggregate-level event sourcing, snapshots, etc.
 *
 * Detection: is_subclass_of($className, AggregateRootInterface::class)
 * No Reflection, no ORM dependency, no trait scanning.
 */
interface AggregateRootInterface
{
}
