<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * Provides a mutable "updated by" UUID column.
 *
 * Mirrors UpdatedAtProviderTrait semantics:
 *   - Property is mutable (no immutability guard)
 *   - Updated by BlameableEntityEventListener on every OnUpdateEvent
 *   - No ORM FK: stores the raw UUID
 */
trait UpdatedByProviderTrait
{
    #[Column(type: 'uuid', nullable: true)]
    public ?Uuid $updatedBy;

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('updatedBy')]
    public ?string $updatedByAsString {
        get => isset($this->updatedBy) ? $this->updatedBy->toString() : null;
    }

    public function __construct(?Uuid $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;
    }
}
