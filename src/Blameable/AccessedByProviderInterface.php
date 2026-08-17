<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use Symfony\Component\Uid\Uuid;

/**
 * Interface for entities that track which actor last accessed them.
 *
 * Nullable to support unauthenticated / anonymous access.
 * Mutable -- updated on every access event.
 */
interface AccessedByProviderInterface
{
    public ?Uuid $accessedBy {
        get;
        set;
    }
    public ?string $accessedByAsString {
        get;
    }
}
