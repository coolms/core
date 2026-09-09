<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

use CoolMS\Core\DataGrid\DataGridDataSource;
use CoolMS\Rql\RqlQuery;

/**
 * Strategy for fetching API-backed datagrid data from a specific route.
 *
 * Implementations are tagged 'coolms.datagrid.repository_provider'.
 */
interface DataGridRepositoryProviderInterface
{
    public function supportsRoute(string $routeName): bool;

    /** @param array<string, mixed> $routeParams */
    public function fetch(array $routeParams, RqlQuery $query, DataGridDataSource $config): DataGridResult;
}
