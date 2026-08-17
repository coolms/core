<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

interface ProviderInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>|string
     */
    public function provide(mixed $data, DataFormat $format = DataFormat::JSON, array $context = []): array|string;
}
