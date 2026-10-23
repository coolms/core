<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable\Fixture;

use CoolMS\Core\Blameable\AccessedByProviderInterface;
use CoolMS\Core\Blameable\AccessedByProviderTrait;
use Symfony\Component\Uid\Uuid;

/**
 * A record that remembers which actor last read it, written the way a consumer
 * writes one: it keeps its own constructor and calls the trait's under an alias.
 */
final class AccessedByRecord implements AccessedByProviderInterface
{
    use AccessedByProviderTrait {
        AccessedByProviderTrait::__construct as private __accessedByConstruct;
    }

    public function __construct(
        public string $title = '',
        ?Uuid $accessedBy = null,
    ) {
        $this->__accessedByConstruct($accessedBy);
    }
}
