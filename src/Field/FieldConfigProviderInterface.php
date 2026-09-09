<?php

declare(strict_types=1);
namespace CoolMS\Core\Field;

/**
 * Provides module-level field config overrides for a set of entity aliases.
 *
 * Modules implement this to override #[FieldMeta] defaults via config files
 * (YAML, XML, or PHP). DB layer (DefinitionRepository) wins over this layer.
 *
 * Tagged: coolms.field.config_provider (registered via Field Extension).
 */
interface FieldConfigProviderInterface
{
    /**
     * Returns field config overrides for the given entity alias, or null
     * when this provider has no config for that alias.
     *
     * Return format (keyed by field name):
     *   [
     *     'title' => ['label' => 'Title', 'formType' => 'text', 'sortOrder' => 0],
     *     'price' => ['label' => 'Price', 'formType' => 'number'],
     *   ]
     *
     * @return array<string, array<string, mixed>>|null
     */
    public function getConfig(string $entityAlias): ?array;
}
