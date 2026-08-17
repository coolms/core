<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait UpdatedAtProviderTrait
{
    #[Column(type: 'datetime_immutable')]
    public DateTimeInterface $updatedAt;

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('updatedAt')]
    public ?string $updatedAtAsString {
        get => isset($this->createdAt) ? $this->updatedAt->format('c') : null;
    }

    public function __construct()
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
