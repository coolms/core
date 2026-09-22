<?php

declare(strict_types=1);

namespace CoolMS\Core\DataGrid;

use CoolMS\Rql\RqlContext;

/**
 * Builds the RQL security context of a server-side-paginated endpoint from the
 * data grid declaration that drives its client-side grid, so ONE declaration
 * governs both the rendered columns and what the API accepts: a column with
 * a filter operation is filterable, a column marked sortable is sortable, and
 * nothing else is.
 *
 * A list endpoint depends on this contract; the DataGrid module provides the
 * implementation over its configuration registry and the alias. The contract
 * sits beside {@see DataGridConfig} because the modules whose endpoints need
 * it sit at every level of the platform, some below DataGrid.
 */
interface RqlContextFactoryInterface
{
    /**
     * @param string $gridId      the data grid declaration's id (`identity:groups`, for example)
     * @param string $entityAlias the query alias the endpoint's repository selects from
     */
    public function create(string $gridId, string $entityAlias): RqlContext;
}
