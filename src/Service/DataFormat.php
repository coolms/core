<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

enum DataFormat: string
{
    case ARRAY = 'array';
    case JSON = 'json';
    case XML = 'xml';
    case YAML = 'yaml';
}
