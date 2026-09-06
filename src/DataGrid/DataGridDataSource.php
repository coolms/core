<?php

declare(strict_types=1);
namespace CoolMS\Core\DataGrid;

use CoolMS\Core\DataGrid\DataGridSourceType;

final readonly class DataGridDataSource
{
    /**
     * @param array<string, mixed>             $routeParams
     * @param array<int, array<string, mixed>> $data
     */
    public function __construct(
        public DataGridSourceType $type,
        // api:
        public ?string $route = null,
        public array $routeParams = [],
        public ?string $defaultSort = null,
        public int $pageSize = 20,
        // file:
        public ?string $path = null,
        public ?string $url = null,
        public string $format = 'csv',
        public string $delimiter = ',',
        public bool $hasHeader = true,
        public string $encoding = 'utf-8',
        // static:
        public array $data = [],
    ) {
    }
}
