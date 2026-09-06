<?php

declare(strict_types=1);
namespace CoolMS\Core\Media;

use CoolMS\Core\Media\ThumbnailConfig;

interface ImageProcessorInterface
{
    public function supports(string $mimeType): bool;

    /** Higher = preferred (Imagick=10, GD=0). */
    public function getPriority(): int;

    public function resize(
        string $inputPath,
        string $outputPath,
        ThumbnailConfig $config,
    ): void;

    public function detectColorspace(string $path): string;

    /** @return array{width: int, height: int, exif: array<string, mixed>} */
    public function extractMetadata(string $path): array;
}
