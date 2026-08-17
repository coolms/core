<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

use Symfony\Component\Yaml\Yaml;

class YamlFileLoader implements FileFormatLoaderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array
    {
        return Yaml::parseFile($path) ?? [];
    }

    public function supports(string $extension): bool
    {
        return in_array($extension, ['yaml', 'yml'], true);
    }
}
