<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

/**
 * Fired when entities are batch-reordered.
 * Thin -- carries only IDs and new positions.
 */
final readonly class EntityReordered
{
    /**
     * @param class-string       $entityClass  e.g., NaviNode::class
     * @param array<string, int> $idToPosition ID to new sortOrder
     */
    public function __construct(
        public string $entityClass,
        public array $idToPosition,
    ) {
    }
}
