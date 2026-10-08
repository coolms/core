<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable\Fixture;

use CoolMS\Core\Timestampable\CreatedAtProviderInterface;
use CoolMS\Core\Timestampable\CreatedAtProviderTrait;
use DateTimeInterface;

/**
 * A record stamped with the moment it was made, written the way a consumer
 * writes one: it keeps its own constructor and calls the trait's under an alias.
 */
final class CreatedAtRecord implements CreatedAtProviderInterface
{
    use CreatedAtProviderTrait {
        CreatedAtProviderTrait::__construct as private __createdAtConstruct;
    }

    public function __construct(
        public string $title = '',
        ?DateTimeInterface $createdAt = null,
    ) {
        $this->__createdAtConstruct($createdAt);
    }
}
