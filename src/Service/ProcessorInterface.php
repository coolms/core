<?php

declare(strict_types=1);

namespace CoolMS\Core\Service;

interface ProcessorInterface
{
    /**
     * @param array<string, mixed>|string $data
     * @param array<string, mixed>        $context
     */
    public function process(array|string $data, ?DataFormat $format = null, array $context = []): object;
}
