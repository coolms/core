<?php

declare(strict_types=1);

namespace CoolMS\Core\Hierarchy;

use Symfony\Component\Uid\Uuid;

/**
 * Entity-agnostic contract for Nested Set tree mutations.
 *
 * All operations are scoped to a single tree identified by $treeId.
 * Implementations must use DQL (not raw SQL) so that mutations are
 * portable across database vendors.
 *
 * Callers are responsible for wrapping related mutations in transactional()
 * and calling lockTree() at the start of each write transaction.
 */
interface NestedSetOperatorInterface
{
    /**
     * Acquire a pessimistic write lock on all nodes in the tree.
     * Must be the first call inside every transactional() closure that
     * performs write operations.
     */
    public function lockTree(Uuid $treeId): void;

    /**
     * Return the current maximum rgt value across all nodes in the tree,
     * or 0 if the tree is empty.
     */
    public function getMaxRgt(Uuid $treeId): int;

    /**
     * Increment rgt of all nodes where rgt >= $threshold by $delta.
     */
    public function shiftRight(Uuid $treeId, int $threshold, int $delta): void;

    /**
     * Increment lft of all nodes where lft > $threshold by $delta.
     */
    public function shiftLeft(Uuid $treeId, int $threshold, int $delta): void;

    /**
     * Delete all nodes with lft >= $lft AND rgt <= $rgt (the subtree rooted at a node).
     */
    public function deleteSubtree(Uuid $treeId, int $lft, int $rgt): void;

    /**
     * Add $delta to both lft and rgt of all nodes in the range [$lft, $rgt].
     * Used to move a subtree as a unit (e.g., into negative space and back).
     */
    public function shiftSubtree(Uuid $treeId, int $lft, int $rgt, int $delta): void;

    /**
     * Add $levelOffset to the level of all nodes in the range [$lft, $rgt].
     * No-op when $levelOffset === 0.
     */
    public function updateSubtreeLevel(Uuid $treeId, int $lft, int $rgt, int $levelOffset): void;

    /**
     * Rebuild lft / rgt / level indexes for the subtree rooted at $root
     * using a PHP-side DFS traversal. Flushes the EntityManager when done.
     *
     * Use this for recovery from index corruption, not for normal insertions.
     */
    public function rebuildIndexes(NestedSetNodeInterface $root): void;

    /**
     * Wrap $fn in a database transaction.
     * The callable receives no arguments. Exceptions propagate and roll back.
     */
    public function transactional(callable $fn): void;
}
