<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Install\Fixture;

use CoolMS\Core\Install\DeclaresPrerequisitesInterface;

/**
 * Named, not anonymous, and named to sort FIRST.
 *
 * The test this exists for turns on class-name order: an anonymous class gets a
 * generated name, so the case could not be stated with one.
 */
final class AlphaRequiresOmega implements DeclaresPrerequisitesInterface
{
    public function declaredRequirements(): array
    {
        return ['thing:omega-made-this'];
    }

    public function declaredProvisions(): array
    {
        return [];
    }
}
