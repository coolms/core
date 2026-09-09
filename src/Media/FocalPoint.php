<?php

declare(strict_types=1);
namespace CoolMS\Core\Media;

use InvalidArgumentException;

final class FocalPoint
{
    // Virtual hook: no backing store -- computed from x/y
    public string $cssObjectPosition {
        get => sprintf('%s%% %s%%', round($this->x * 100), round($this->y * 100));
    }

    public function __construct(
        public readonly float $x, // 0.0-1.0
        public readonly float $y, // 0.0-1.0
    ) {
        if ($x < 0.0 || $x > 1.0 || $y < 0.0 || $y > 1.0) {
            throw new InvalidArgumentException('FocalPoint coordinates must be between 0 and 1.');
        }
    }

    public static function center(): self
    {
        return new self(0.5, 0.5);
    }

    /**
     * @param array{0?: float, 1?: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self((float) ($data[0] ?? 0.5), (float) ($data[1] ?? 0.5));
    }

    public function toCssObjectPosition(): string
    {
        return $this->cssObjectPosition;
    }

    /** @return array{float, float} */
    public function toArray(): array
    {
        return [$this->x, $this->y];
    }
}
