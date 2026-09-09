<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

use Symfony\Component\Yaml\Yaml;

/**
 * Writes form config to a YAML file using Symfony's Yaml component.
 *
 * If the target file already exists its content is deep-merged with the
 * incoming data so that manually maintained keys (comments, extra options)
 * survive an automated update.
 *
 * Output format:
 *   config/modules/{module}/forms/{formId}.yaml
 */
final readonly class YamlConfigWriter implements ConfigWriterInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function write(string $formId, array $data, string $directory, bool $replace = false): string
    {
        $path = rtrim($directory, '/') . "/$formId.yaml";

        // On a sparse update, deep-merge onto the existing file so manually
        // maintained keys survive. On an authoritative replace, OVERWRITE -- else
        // a deleted field / a shortened layout would be resurrected by the merge.
        if (!$replace && file_exists($path)) {
            $existing = Yaml::parseFile($path) ?? [];
            $data = $this->deepMerge($existing, $data);
        }

        $this->ensureDirectory($directory);

        file_put_contents($path, Yaml::dump($data, inline: 4, indent: 2));

        return $path;
    }

    public function getFormat(): string
    {
        return 'yaml';
    }

    public function supports(string $format): bool
    {
        return in_array($format, ['yaml', 'yml'], strict: true);
    }

    /**
     * Recursive deep merge: $override wins for scalar values; arrays are merged
     * recursively so nested options sub-keys are preserved.
     *
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    private function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (isset($base[$key]) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->deepMerge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, permissions: 0o755, recursive: true);
        }
    }
}
