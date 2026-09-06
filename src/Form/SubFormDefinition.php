<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class SubFormDefinition
{
    public function __construct(
        public string $formId,
        public RelationCardinality $relation = RelationCardinality::One,
        public ?int $maxItems = null,
    ) {
    }
}
