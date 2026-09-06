<?php

declare(strict_types=1);
namespace CoolMS\Core\Field;

/**
 * Registers entity alias -> class mappings for StaticFieldMetaLoader.
 *
 * Each module that wants #[FieldMeta] fields to appear in forms
 * implements this interface and returns its formId -> class map.
 *
 * Tag: coolms.field.static_entity_alias_provider
 * (registered in Field\Infrastructure\DependencyInjection\Extension via
 * registerForAutoconfiguration())
 */
interface StaticEntityAliasProviderInterface
{
    /**
     * @return array<string, class-string> formId (entityAlias) => PHP class
     */
    public function getAliases(): array;
}
