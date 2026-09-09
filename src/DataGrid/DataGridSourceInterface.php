<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

use CoolMS\Core\DataGrid\DataGridDataSource;
use CoolMS\Rql\RqlQuery;

interface DataGridSourceInterface
{
    public function supports(DataGridDataSource $source): bool;

    public function fetch(DataGridDataSource $source, RqlQuery $query): DataGridResult;
}
