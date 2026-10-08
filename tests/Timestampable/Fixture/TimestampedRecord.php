<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Timestampable\Fixture;

use CoolMS\Core\Timestampable\TimestampableInterface;
use CoolMS\Core\Timestampable\TimestampableTrait;

/**
 * A record carrying all three timestamps, written the way a consumer writes one:
 * its own constructor calls the composite trait's under an alias.
 */
final class TimestampedRecord implements TimestampableInterface
{
    use TimestampableTrait {
        TimestampableTrait::__construct as private __timestampableConstruct;
    }

    public function __construct(
        public string $title = '',
    ) {
        $this->__timestampableConstruct();
    }
}
