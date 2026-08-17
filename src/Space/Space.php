<?php

declare(strict_types=1);

namespace CoolMS\Core\Space;

/**
 * Concrete Space value object -- emitted by tagged providers, consumed
 * by per-module {@see \CoolMS\CoreModule\Space\SpaceRegistry} subclasses
 * (e.g. `MediaSpaceRegistry`, `DocumentSpaceRegistry`) and projected
 * onto module-specific API resources.
 */
final readonly class Space implements SpaceInterface
{
    public function __construct(
        public string $key,
        public string $label,
        public string $rootPath,
        public bool $isWritable,
        public int $priority,
        public ?string $badge = null,
    ) {
    }
}
