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
 * Provides a mutable "accessed by" UUID column.
 *
 * Mirrors AccessedAtProviderTrait semantics:
 *   - Property is mutable (no immutability guard)
 *   - Updated by BlameableEntityEventListener on every OnCreateEvent (read tracking)
 *   - No ORM FK: stores the raw UUID
 */
trait AccessedByProviderTrait
{
    #[Column(type: 'uuid', nullable: true)]
    public ?Uuid $accessedBy;

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('accessedBy')]
    public ?string $accessedByAsString {
        get => isset($this->accessedBy) ? $this->accessedBy->toString() : null;
    }

    public function __construct(?Uuid $accessedBy = null)
    {
        $this->accessedBy = $accessedBy;
    }
}
