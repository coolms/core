<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

enum DataGridSourceType: string
{
    case Api = 'api';
    case File = 'file';
    case Static = 'static';
}
