<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class DataSourceOption
{
    public function __construct(
        public mixed $value,
        public string $label,
        public ?string $parentId = null, // for select-tree widget: client-side tree building
    ) {
    }
}
