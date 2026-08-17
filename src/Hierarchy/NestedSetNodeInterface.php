<?php

declare(strict_types=1);

namespace CoolMS\Core\Hierarchy;

interface NestedSetNodeInterface
{
    /** Left boundary index of the nested set range. */
    public int $lft {
        get;
        set;
    }

    /** Right boundary index of the nested set range. */
    public int $rgt {
        get;
        set;
    }

    /** Depth level in the tree (root = 0). */
    public int $level {
        get;
        set;
    }

    /**
     * Direct children of this node.
     * Required by rebuildIndexes() DFS traversal.
     * Implementing classes typically back this with an ORM-managed collection.
     *
     * @var iterable<int, static>
     */
    public iterable $children {
        get;
    }

    /**
     * Direct parent of this node, or null if this is a root node.
     * Child interfaces that extend this one MAY narrow the return type covariantly
     * since only { get; } is declared (PHP 8.4+ covariant property hooks).
     */
    public ?self $parent {
        get;
    }
}
