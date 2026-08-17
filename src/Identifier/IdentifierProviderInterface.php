<?php

declare(strict_types=1);

namespace CoolMS\Core\Identifier;

use Symfony\Component\Uid\Uuid;

interface IdentifierProviderInterface
{
    public Uuid $id {
        get;
    }
}
