<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\VariableType;

/**
 * One entry in `process.variables{}`. Variables are process-root scoped
 * in M2 (see `docs/investigations/m2c-design.md` section 3.5 -- embedded
 * subprocess scopes defer to M3).
 *
 * The `enum` field is the closed value list for `VariableType::Enum`;
 * the validator (`WF.ENUM_REQUIRES_VALUES`) rejects a missing or empty
 * list when `type === Enum`. The section 3.3 example uses `type: 'enum'`; the
 * parser also accepts the equivalent `type: 'string' + enum: [...]`
 * spelling (section 2.6 / section 10 Q1).
 *
 * `default` is a literal in the declared type -- never an EL expression.
 * `WF.VAR_DEFAULT_TYPE_MISMATCH` enforces shape at deploy time.
 */
final readonly class VariableDeclaration
{
    /**
     * @param ?list<string|int|float|bool> $enum value list for `VariableType::Enum`; null for other types
     */
    public function __construct(
        public string $name,
        public VariableType $type,
        public bool $required = false,
        public ?array $enum = null,
        public mixed $default = null,
        public ?string $label = null,
    ) {
    }
}
