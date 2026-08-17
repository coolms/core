<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

use LogicException;

class ImmutablePropertyException extends LogicException
{
    public function __construct(string $property, string $class)
    {
        parent::__construct("$class::$property cannot be set after initialization");
    }
}
