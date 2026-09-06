<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

/**
 * Writes form config as a PHP file that returns a plain array.
 *
 * Output:
 *   <?php
 *
 *   return [
 *       'type' => 'form',
 *       'id' => '…',
 *       'fields' => [ … ],
 *   ];
 *
 * If the target file already exists, it is loaded via `require` and the result
 * is deep-merged with the incoming data before writing, preserving manual edits.
 */
final readonly class PhpConfigWriter implements ConfigWriterInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function write(string $formId, array $data, string $directory, bool $replace = false): string
    {
        $path = rtrim($directory, '/') . "/$formId.php";

        // Sparse update deep-merges to preserve manual edits; an authoritative
        // replace overwrites so removed keys actually disappear.
        if (!$replace && file_exists($path)) {
            $existing = require $path;
            if (is_array($existing)) {
                $data = $this->deepMerge($existing, $data);
            }
        }

        $this->ensureDirectory($directory);

        $exported = var_export($data, return: true);
        // Reformat: var_export uses array() syntax in older PHP but 'return' is the key bit
        file_put_contents($path, "<?php\n\ndeclare(strict_types=1);\n\nreturn $exported;\n");

        return $path;
    }

    public function getFormat(): string
    {
        return 'php';
    }

    public function supports(string $format): bool
    {
        return 'php' === $format;
    }

    /**
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
