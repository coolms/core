<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

use LogicException;

class ReadonlyPropertyException extends LogicException
{
    public function __construct(string $property, string $class)
    {
        parent::__construct("$class::$property is read-only");
    }
}
