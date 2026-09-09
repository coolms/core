<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

final readonly class FieldItem
{
    /**
     * @param ValidatorDefinition[] $validators
     * @param string[]|null         $separators separator chars for token-pattern fields (e.g. ['_', '-', '.'])
     */
    public function __construct(
        public string $alias,
        public string $type,       // text|email|password|number|textarea|select|toggle|date|time|relation|subform|hidden|token-pattern
        public string $label,
        public ?string $placeholder = null,
        public ?string $hint = null,
        public bool $required = false,
        public bool $readonly = false,
        public bool $locked = false,
        public bool $private = false,
        public array $validators = [],
        public ?DataSourceDefinition $dataSource = null,
        public ?RelationDefinition $relation = null,
        public ?SubFormDefinition $subForm = null,
        public ?array $separators = null,
        public ?FieldSecurityPolicy $security = null,
        // HTML autocomplete token (e.g. 'username', 'current-password',
        // 'new-password', 'given-name') -- a password-manager / browser-autofill
        // hint, sourced from the field's `options.attr.autocomplete`.
        public ?string $autocomplete = null,
    ) {
    }

    /**
     * Return a copy with the given presentation keys overridden -- the seam a
     * {@see FieldItemAdapterInterface} drives. Constructed here (in the VO's own
     * scope) because readonly properties can only be reinitialised from the
     * declaring class. Only `label`, `type`, `autocomplete` are adaptable.
     *
     * @param array{label?: string, type?: string, autocomplete?: string|null} $overrides
     */
    public function withOverrides(array $overrides): self
    {
        return clone ($this, [
            'type' => $overrides['type'] ?? $this->type,
            'label' => $overrides['label'] ?? $this->label,
            'autocomplete' => array_key_exists('autocomplete', $overrides) ? $overrides['autocomplete'] : $this->autocomplete,
        ]);
    }
}
