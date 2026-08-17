<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use Symfony\Component\Uid\Uuid;

/**
 * Interface for entities that track which actor created them.
 *
 * Nullable to support unauthenticated / system writes.
 * Immutable after first assignment (same semantics as CreatedAtProviderInterface).
 */
interface CreatedByProviderInterface
{
    public ?Uuid $createdBy {
        get;
        set;
    }
    public ?string $createdByAsString {
        get;
    }
}
