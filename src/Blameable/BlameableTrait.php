<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * Composite UUID-based blameable trait for entities.
 *
 * Mirrors TimestampableTrait in structure -- composes the three provider traits
 * and aliases their constructors so the consuming entity can call a single
 * `$this->__blameableConstruct()` from its own constructor.
 *
 * Usage in an entity:
 *
 *   use BlameableTrait { BlameableTrait::__construct as private __blameableConstruct; }
 *
 *   public function __construct(...) {
 *       $this->__blameableConstruct(); // all three -- null; listener fills from Security
 *   }
 *
 * Or pass explicit UUIDs (e.g., from a command / seeder):
 *
 *   $this->__blameableConstruct(createdBy: $adminUuid);
 */
trait BlameableTrait
{
    use CreatedByProviderTrait {
        CreatedByProviderTrait::__construct as private __createdByConstruct;
    }
    use UpdatedByProviderTrait {
        UpdatedByProviderTrait::__construct as private __updatedByConstruct;
    }
    use AccessedByProviderTrait {
        AccessedByProviderTrait::__construct as private __accessedByConstruct;
    }

    public function __construct(
        ?Uuid $createdBy = null,
        ?Uuid $updatedBy = null,
        ?Uuid $accessedBy = null,
    ) {
        $this->__createdByConstruct($createdBy);
        $this->__updatedByConstruct($updatedBy);
        $this->__accessedByConstruct($accessedBy);
    }
}
