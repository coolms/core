<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

use CoolMS\Core\Form\DataSourceDefinition;

/**
 * Resolves a raw dataSource config array from YAML into a DataSourceDefinition.
 *
 * Each module registers its own resolver via the 'coolms.form.datasource_resolver' tag.
 * Tagged as autoconfigurable in FormBundle's DI Extension.
 */
interface DataSourceResolverInterface
{
    /**
     * Return true when this resolver handles the given dataSource config.
     *
     * @param array<string, mixed> $config raw dataSource section from YAML
     */
    public function supports(array $config): bool;

    /**
     * Build a DataSourceDefinition from the raw config.
     *
     * @param array<string, mixed> $config raw dataSource section from YAML
     */
    public function resolve(array $config): DataSourceDefinition;
}
