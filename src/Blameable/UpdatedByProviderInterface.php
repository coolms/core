<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use Symfony\Component\Uid\Uuid;

/**
 * Interface for entities that track which actor last updated them.
 *
 * Nullable to support unauthenticated / system writes.
 * Mutable -- updated on every write event.
 */
interface UpdatedByProviderInterface
{
    public ?Uuid $updatedBy {
        get;
        set;
    }
    public ?string $updatedByAsString {
        get;
    }
}
