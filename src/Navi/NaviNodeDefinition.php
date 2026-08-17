<?php

declare(strict_types=1);

namespace CoolMS\Core\Navi;

/**
 * Seed definition for a single NaviNode contributed by a module.
 * Used by NaviGraphContributorInterface implementations.
 */
final readonly class NaviNodeDefinition
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $path,
        public string $title,
        public array $meta = [],
        public ?string $parentPath = null,
        public int $sortOrder = 0,
        public bool $isVisible = true,
    ) {
    }
}
