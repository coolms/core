<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

/**
 * Profile-specific viewer configuration. The `config` shape is
 * open-ended — each viewer interprets keys it understands (PDF reads
 * `toolbar.show`, `sidebar.tabs`; DOCX reads only `toolbar.buttons`).
 * Generic schema would force a lowest common denominator and rule out
 * legitimate library-specific options.
 */
final readonly class ViewerProfile
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public string $key,
        public array $config,
    ) {
    }

    /**
     * @return array{key: string, config: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'config' => $this->config,
        ];
    }
}
