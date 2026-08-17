<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait AccessedAtProviderTrait
{
    #[Column(type: 'datetime_immutable')]
    public DateTimeInterface $accessedAt;

    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('accessedAt')]
    public ?string $accessedAtAsString {
        get => isset($this->accessedAt) ? $this->accessedAt->format('c') : null;
    }

    public function __construct()
    {
        $this->accessedAt = new DateTimeImmutable();
    }
}
