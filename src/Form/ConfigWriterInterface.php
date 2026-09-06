<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

/**
 * Strategy interface for persisting form/form_type configuration data.
 *
 * Bundled implementations:
 *   YamlConfigWriter -- writes YAML files (default)
 *   PhpConfigWriter  -- writes `return [...];` PHP files
 *   XmlConfigWriter  -- writes XML files
 *
 * Additional implementations may be registered by other modules.
 */
interface ConfigWriterInterface
{
    /**
     * Persist $data as the config for $formId.
     *
     * For file-based writers $directory is the target folder.
     * For the DB writer $directory is ignored (the entity alias is used instead).
     *
     * $replace controls how an EXISTING stored config is treated:
     *   - false (default): a sparse update — file writers deep-merge $data onto
     *     the existing file so manually-maintained keys survive; matches the
     *     `ConfigManager::update()` in the consuming application semantics.
     *   - true: an authoritative full replace — file writers OVERWRITE (no merge),
     *     so a removed field / a shortened array actually disappears. This is the
     *     Form Builder's Save path (`ConfigManager::replace()` in the consuming application).
     * Writers that already overwrite wholesale (the DB override writer) ignore it.
     *
     * @param string               $formId    the form's `id` key (e.g., 'user_profile')
     * @param array<string, mixed> $data      full canonical config array to persist
     * @param string               $directory target folder (file writers) or ignored (DB writer)
     * @param bool                 $replace   true = overwrite/no-merge; false = sparse-merge (default)
     *
     * @return string storage key: absolute file path (file) or $formId (DB)
     */
    public function write(string $formId, array $data, string $directory, bool $replace = false): string;

    /**
     * Short format name used for routing: 'yaml' | 'php' | 'db'.
     */
    public function getFormat(): string;

    /**
     * Return true when this writer handles the given format string.
     * Called by ConfigManager::resolveWriter().
     */
    public function supports(string $format): bool;
}
