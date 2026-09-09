<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class RelationDefinition
{
    public function __construct(
        public RelationCardinality $cardinality,
        public ?int $maxItems = null,
        public ?string $targetFormId = null, // form ID for inline create
        public ?DataSourceDefinition $dataSource = null,
    ) {
    }
}
