<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

class XmlFileLoader implements FileFormatLoaderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array
    {
        $xml = simplexml_load_file($path);
        if (false === $xml) {
            return [];
        }
        $encoded = json_encode($xml);

        return false !== $encoded ? (json_decode($encoded, true) ?? []) : [];
    }

    public function supports(string $extension): bool
    {
        return 'xml' === $extension;
    }
}
