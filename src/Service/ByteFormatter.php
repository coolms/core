<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

/**
 * Utility for formatting byte counts into human-readable strings.
 */
final class ByteFormatter
{
    /**
     * Format a byte count as a human-readable string (e.g., 1.5 MB).
     *
     * @param list<string> $units scale labels, smallest first
     */
    public static function format(
        int $bytes,
        array $units = ['B', 'KB', 'MB', 'GB', 'TB'],
    ): string {
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[(int) $pow];
    }
}
