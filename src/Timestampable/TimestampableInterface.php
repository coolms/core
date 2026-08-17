<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

interface TimestampableInterface extends AccessedAtProviderInterface, CreatedAtProviderInterface, UpdatedAtProviderInterface
{
}
