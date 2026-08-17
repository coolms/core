<?php

declare(strict_types=1);

namespace CoolMS\Core\Serializer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class DateTimeObjectDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        // If the data is already an object of the required type -- return it
        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        // Check if the data is an object of the required type
        return is_a($type, DateTimeInterface::class, true) && $data instanceof DateTimeInterface;
        // return is_a($type, DateTimeInterface::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DateTimeInterface::class => true,
            DateTimeImmutable::class => true,
            DateTime::class => true,
        ];
    }
}
