<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

final readonly class DataGridResult
{
    /**
     * @param array<array<string, mixed>> $items
     */
    public function __construct(
        public array $items,
        public int $totalItems,
        public int $page,
        public int $limit,
    ) {
    }

    public function totalPages(): int
    {
        return $this->limit > 0 ? (int) ceil($this->totalItems / $this->limit) : 1;
    }
}
