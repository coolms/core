<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Identifier\Fixture;

use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use Symfony\Component\Uid\Uuid;

/**
 * A record identified by a UUID, written the way a consumer writes one: its own
 * constructor calls the trait's under an alias, minting a time-ordered UUID when
 * the caller does not bring one.
 */
final class IdentifiedRecord implements IdentifierProviderInterface
{
    use IdentifierProviderTrait {
        IdentifierProviderTrait::__construct as private __identifierConstruct;
    }

    public function __construct(
        public string $title = '',
        ?Uuid $id = null,
    ) {
        $this->__identifierConstruct($id ?? Uuid::v7());
    }
}
