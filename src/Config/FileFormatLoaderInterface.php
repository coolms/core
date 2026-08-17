<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

/**
 * Strategy interface for file-format-specific config parsers.
 *
 * Implementations handle a single format (YAML, XML, PHP) and are composed
 * by a chain-loader to read configuration files.
 */
interface FileFormatLoaderInterface
{
    /**
     * Parse the file at $path and return its data as an associative array.
     *
     * @return array<string, mixed>
     */
    public function load(string $path): array;

    /** Return true when this loader handles files with the given extension. */
    public function supports(string $extension): bool;
}
