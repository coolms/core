<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait CreatedAtProviderTrait
{
    #[Column(type: 'datetime_immutable')]
    public DateTimeInterface $createdAt;

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('createdAt')]
    public ?string $createdAtAsString {
        get => isset($this->createdAt) ? $this->createdAt->format('c') : null;
    }

    public function __construct(?DateTimeInterface $createdAt = null)
    {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }
}
