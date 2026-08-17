<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

class PhpFileLoader implements FileFormatLoaderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array
    {
        $result = require $path;

        return is_array($result) ? $result : [];
    }

    public function supports(string $extension): bool
    {
        return 'php' === $extension;
    }
}
