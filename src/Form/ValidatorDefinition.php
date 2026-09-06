<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class ValidatorDefinition
{
    public function __construct(
        public string $type,           // NotBlank, Length, Range, Email, Regex...
        public mixed $value = null, // constraint parameter (max, min, pattern...)
        public ?string $message = null,
    ) {
    }
}
