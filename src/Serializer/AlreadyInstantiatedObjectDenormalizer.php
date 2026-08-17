<?php

declare(strict_types=1);

namespace CoolMS\Core\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AlreadyInstantiatedObjectDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        // Just return the object as is
        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        // Supports denormalization if the data is already an object of the required type
        return is_object($data) && is_a($data, $type);
    }

    public function getSupportedTypes(?string $format): array
    {
        // Pointing to the object type is enough
        return ['object' => true];
    }
}
