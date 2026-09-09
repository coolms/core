<?php

declare(strict_types=1);
namespace CoolMS\Core\Media;

final readonly class ThumbnailConfig
{
    public function __construct(
        public string $name,       // 'small', 'medium', 'large', 'print'
        public ?int $width,      // null = proportional
        public ?int $height,     // null = proportional
        public string $mode,       // 'fit' | 'fill' | 'max'
        public string $format,     // 'webp' | 'jpeg' | 'png' | 'tiff'
        public int $quality,    // 0-100
        public string $colorspace, // 'srgb' | 'cmyk'
        // Per-call focal hint passed to the image processor; honoured
        // by the 'fill' mode to crop around the subject rather than
        // the geometric centre.
        public ?FocalPoint $focalPoint = null,
    ) {
    }

    public function cacheKey(string $uuid): string
    {
        return sprintf('%s_%s.%s', $uuid, $this->name, $this->format);
    }

    /**
     * Return a copy of this config with the focal point set.
     * The handler passes the per-Node focal extras into each
     * configured preset via this helper.
     */
    public function withFocalPoint(?FocalPoint $focal): self
    {
        return clone ($this, ['focalPoint' => $focal]);
    }
}
