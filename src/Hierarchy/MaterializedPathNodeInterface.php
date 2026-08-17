<?php

declare(strict_types=1);

namespace CoolMS\Core\Hierarchy;

/**
 * Contract for entities that participate in a Materialized Path hierarchy.
 *
 * The full path is stored in $path (e.g., "/root/parent/self").
 * The single segment contributed by this node is exposed via $pathSegment;
 * implementations may return an ID, slug, or name as appropriate.
 */
interface MaterializedPathNodeInterface
{
    /**
     * Full materialized path from root to this node, including leading separator.
     * Example: "/electronics/phones/smartphones".
     */
    public string $path {
        get;
        set;
    }

    /**
     * The single path segment that this node contributes.
     * Hook -- implementations decide whether to return ID, slug, or name.
     * Must not contain the path separator character.
     */
    public string $pathSegment {
        get;
    }

    /**
     * Direct parent, or null for root nodes.
     * Child interfaces may narrow this type covariantly (get-only, PHP 8.4+).
     */
    public ?self $parent {
        get;
    }
}
