<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable\Fixture;

use CoolMS\Core\Timestampable\AccessedAtProviderInterface;
use CoolMS\Core\Timestampable\AccessedAtProviderTrait;

/**
 * A record stamped with the moment it was last read, written the way a consumer
 * writes one: it keeps its own constructor and calls the trait's under an alias.
 */
final class AccessedAtRecord implements AccessedAtProviderInterface
{
    use AccessedAtProviderTrait {
        AccessedAtProviderTrait::__construct as private __accessedAtConstruct;
    }

    public function __construct(
        public string $title = '',
    ) {
        $this->__accessedAtConstruct();
    }
}
