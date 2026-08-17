<?php

declare(strict_types=1);

namespace CoolMS\Core\Hierarchy;

/**
 * Entity-agnostic contract for Materialized Path tree mutations.
 *
 * Implementations handle path computation and bulk subtree re-pathing.
 * The path separator character is an implementation detail (defaults to '/').
 */
interface MaterializedPathOperatorInterface
{
    /**
     * Compute and set $node->path based on the parent chain.
     *
     * - Root node (parent === null): path = separator + node->pathSegment
     * - Child node: path = parent->path + separator + node->pathSegment
     *
     * Callers must ensure $node->parent is loaded before calling this method.
     */
    public function updatePath(MaterializedPathNodeInterface $node): void;

    /**
     * Bulk-update the stored path for all descendants of $node after a move.
     *
     * $oldPath is the node's path BEFORE the move.
     * $node->path must already reflect the new position.
     *
     * All descendant rows with path LIKE '$oldPath%' will have the
     * $oldPath prefix replaced with $node->path.
     */
    public function moveSubtree(MaterializedPathNodeInterface $node, string $oldPath): void;
}
