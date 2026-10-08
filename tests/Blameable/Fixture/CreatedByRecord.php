<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable\Fixture;

use CoolMS\Core\Blameable\CreatedByProviderInterface;
use CoolMS\Core\Blameable\CreatedByProviderTrait;
use Symfony\Component\Uid\Uuid;

/**
 * A record that remembers which actor created it, written the way a consumer
 * writes one: it keeps its own constructor and calls the trait's under an alias.
 */
final class CreatedByRecord implements CreatedByProviderInterface
{
    use CreatedByProviderTrait {
        CreatedByProviderTrait::__construct as private __createdByConstruct;
    }

    public function __construct(
        public string $title = '',
        ?Uuid $createdBy = null,
    ) {
        $this->__createdByConstruct($createdBy);
    }
}
