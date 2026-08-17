<?php

declare(strict_types=1);

namespace CoolMS\Core\Identifier;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use CoolMS\Core\Mapping\GeneratedValue;
use CoolMS\Core\Mapping\Id;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

trait IdentifierProviderTrait
{
    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('id')]
    public ?string $idAsString {
        get => isset($this->id) ? $this->id->toString() : null;
    }

    public function __construct(
        #[Id]
        #[Column(type: 'uuid', unique: true)]
        #[GeneratedValue(
            strategy: GeneratedValue::CUSTOM,
            generator: GeneratedValue::UUID_V7,
        )]
        public Uuid $id,
    ) {
    }
}
