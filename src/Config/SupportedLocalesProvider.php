<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

final readonly class SupportedLocalesProvider
{
    /**
     * @param array<array{code: string, label: string}> $locales
     */
    public function __construct(private array $locales)
    {
    }

    /** @return array<array{code: string, label: string}> */
    public function all(): array
    {
        return $this->locales;
    }

    public function default(): string
    {
        return $this->locales[0]['code'] ?? 'en';
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_column($this->locales, 'code');
    }
}
