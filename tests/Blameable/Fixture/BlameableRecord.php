<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable\Fixture;

use CoolMS\Core\Blameable\BlameableInterface;
use CoolMS\Core\Blameable\BlameableTrait;
use Symfony\Component\Uid\Uuid;

/**
 * A record carrying all three blame fields, written the way a consumer writes
 * one: its own constructor calls the composite trait's under an alias, naming
 * each actor it passes, as a command or a seeder does.
 */
final class BlameableRecord implements BlameableInterface
{
    use BlameableTrait {
        BlameableTrait::__construct as private __blameableConstruct;
    }

    public function __construct(
        public string $title = '',
        ?Uuid $createdBy = null,
        ?Uuid $updatedBy = null,
        ?Uuid $accessedBy = null,
    ) {
        $this->__blameableConstruct(createdBy: $createdBy, updatedBy: $updatedBy, accessedBy: $accessedBy);
    }
}
