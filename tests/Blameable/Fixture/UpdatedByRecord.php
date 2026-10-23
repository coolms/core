<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Blameable\Fixture;

use CoolMS\Core\Blameable\UpdatedByProviderInterface;
use CoolMS\Core\Blameable\UpdatedByProviderTrait;
use Symfony\Component\Uid\Uuid;

/**
 * A record that remembers which actor last changed it, written the way a
 * consumer writes one: it keeps its own constructor and calls the trait's under
 * an alias.
 */
final class UpdatedByRecord implements UpdatedByProviderInterface
{
    use UpdatedByProviderTrait {
        UpdatedByProviderTrait::__construct as private __updatedByConstruct;
    }

    public function __construct(
        public string $title = '',
        ?Uuid $updatedBy = null,
    ) {
        $this->__updatedByConstruct($updatedBy);
    }
}
