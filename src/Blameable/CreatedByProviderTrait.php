<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Exception\ImmutablePropertyException;
use CoolMS\Core\Mapping\Column;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * Provides an immutable "created by" UUID column.
 *
 * Mirrors CreatedAtProviderTrait semantics:
 *   - Property is write-once (null -- Uuid allowed; Uuid -- Uuid throws ImmutablePropertyException)
 *   - `isset(null) === false`, so null-initialized properties can still be set by the EventListener
 *   - No ORM FK: stores the raw UUID, making this trait cross-module and microservice-portable
 */
trait CreatedByProviderTrait
{
    #[Column(type: 'uuid', nullable: true)]
    public ?Uuid $createdBy {
        get => $this->createdBy;
        set => isset($this->createdBy)
            ? throw new ImmutablePropertyException(__PROPERTY__, static::class) : $value;
    }

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('createdBy')]
    public ?string $createdByAsString {
        get => isset($this->createdBy) ? $this->createdBy->toString() : null;
    }

    public function __construct(?Uuid $createdBy = null)
    {
        $this->createdBy = $createdBy;
    }
}
