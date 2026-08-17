<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

trait ExpiresAtProviderTrait
{
    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('e')]
    public ?string $expiresAtAsString {
        get => isset($this->expiresAt) ? $this->expiresAt->format('c') : null;
    }

    #[FieldMeta(private: true)]
    public bool $isExpired {
        get => isset($this->expiresAt) && $this->expiresAt <= new DateTimeImmutable();
    }

    public function __construct(
        #[Column(type: 'datetime_immutable')]
        public DateTimeInterface $expiresAt,
    ) {
    }
}
