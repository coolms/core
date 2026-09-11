<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install\Fixture;

use CoolMS\Core\Install\DeclaresPrerequisitesInterface;

/**
 * Named to sort LAST, so that running it first can only be the result of the
 * declarations being read.
 */
final class OmegaProvides implements DeclaresPrerequisitesInterface
{
    public function declaredRequirements(): array
    {
        return [];
    }

    public function declaredProvisions(): array
    {
        return ['thing:omega-made-this'];
    }
}
