<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Exception\ImmutablePropertyException;
use CoolMS\Core\Mapping\Column;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The moment a record was made: set once, by its constructor or by whatever
 * builds it, and never replaced. A second write throws
 * ImmutablePropertyException and leaves the first value in place, the same
 * semantics as CreatedByProviderTrait.
 *
 * A persistence layer that writes the raw value
 * (`ReflectionProperty::setRawValue()`, PHP 8.4 and later) does not pass
 * through the guard, so loading and refreshing a stored record still work.
 */
trait CreatedAtProviderTrait
{
    #[Column(type: 'datetime_immutable')]
    public DateTimeInterface $createdAt {
        set => isset($this->createdAt)
            ? throw new ImmutablePropertyException(__PROPERTY__, static::class) : $value;
    }

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
