<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

enum RelationCardinality: string
{
    case One = 'one';
    case Many = 'many';
}
