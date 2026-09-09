<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

use DOMDocument;
use DOMElement;

/**
 * Writes form config as an XML file.
 *
 * Output structure mirrors what XmlConfigLoader reads back via
 * simplexml_load_file() + JSON round-trip:
 *
 *   <?xml version="1.0" encoding="UTF-8"?>
 *   <config>
 *     <type>form</type>
 *     <id>user_profile</id>
 *     <fields>
 *       <email>
 *         <type>email</type>
 *         <options><label>Your Email</label></options>
 *       </email>
 *     </fields>
 *   </config>
 *
 * If the target file already exists, it is parsed and deep-merged with the
 * incoming data before writing, preserving manual edits.
 */
final readonly class XmlConfigWriter implements ConfigWriterInterface
{
    public function write(string $formId, array $data, string $directory, bool $replace = false): string
    {
        $path = rtrim($directory, '/') . "/$formId.xml";

        // Sparse update deep-merges to preserve manual edits; an authoritative
        // replace overwrites so removed keys actually disappear.
        if (!$replace && file_exists($path)) {
            $xml = simplexml_load_file($path);
            if (false !== $xml) {
                $encoded = json_encode($xml);
                $existing = false !== $encoded ? (json_decode($encoded, associative: true) ?? []) : [];
                $data = $this->deepMerge($existing, $data);
            }
        }

        $this->ensureDirectory($directory);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElement('config');
        $dom->appendChild($root);
        $this->arrayToXml($data, $root, $dom);

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    public function getFormat(): string
    {
        return 'xml';
    }

    public function supports(string $format): bool
    {
        return 'xml' === $format;
    }

    /**
     * Recursively convert an associative array to child DOM elements.
     * Scalar values become text nodes; arrays recurse.
     */
    /** @param array<mixed> $data */
    private function arrayToXml(array $data, DOMElement $parent, DOMDocument $dom): void
    {
        foreach ($data as $key => $value) {
            $element = $dom->createElement((string) $key);
            $parent->appendChild($element);
            if (is_array($value)) {
                $this->arrayToXml($value, $element, $dom);
            } else {
                $element->appendChild($dom->createTextNode((string) $value));
            }
        }
    }

    /**
     * @param array<mixed> $base
     * @param array<mixed> $override
     *
     * @return array<mixed>
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
