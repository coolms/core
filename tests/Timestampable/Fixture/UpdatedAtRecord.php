<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable\Fixture;

use CoolMS\Core\Timestampable\UpdatedAtProviderInterface;
use CoolMS\Core\Timestampable\UpdatedAtProviderTrait;

/**
 * A record stamped with the moment it last changed, and with nothing else: it
 * declares no creation time of its own. Written the way a consumer writes one,
 * keeping its own constructor and calling the trait's under an alias.
 */
final class UpdatedAtRecord implements UpdatedAtProviderInterface
{
    use UpdatedAtProviderTrait {
        UpdatedAtProviderTrait::__construct as private __updatedAtConstruct;
    }

    public function __construct(
        public string $title = '',
    ) {
        $this->__updatedAtConstruct();
    }
}
