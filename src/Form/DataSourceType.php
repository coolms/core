<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

enum DataSourceType: string
{
    case Static = 'static';
    case Enum = 'enum';
    case Api = 'api';
    case Repo = 'repo'; // future — requires RQL module
}
