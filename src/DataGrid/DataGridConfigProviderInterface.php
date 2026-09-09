<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

use CoolMS\Core\DataGrid\DataGridConfig;

interface DataGridConfigProviderInterface
{
    /**
     * Returns a DataGridConfig for the given id, or null if this
     * provider does not handle it.
     */
    public function provide(string $id): ?DataGridConfig;
}
