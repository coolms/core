<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Closed catalogue of process-variable types per
 * `docs/investigations/m2c-design.md` section 2.6 / section 3.1 (G13).
 *
 * - `string`   -- UTF-8 string. Also accepts an `enum: [...]` sibling
 *                as the equivalent of declaring `type: enum`.
 * - `int`      -- 64-bit integer.
 * - `float`    -- IEEE-754 double.
 * - `bool`     -- true / false.
 * - `uuid`     -- RFC 4122 string (Identity refs).
 * - `datetime` -- ISO-8601 datetime.
 * - `date`     -- ISO-8601 date.
 * - `duration` -- ISO-8601 duration (consumed by timer / dueDate).
 * - `enum`     -- Closed value list (requires non-empty `enum: [...]`).
 * - `json`     -- Any JSON blob; escape hatch for nested payloads.
 *
 * `default` values are literals in the declared type (never EL); the
 * validator type-checks via rule `WF.VAR_DEFAULT_TYPE_MISMATCH`.
 */
enum VariableType: string
{
    case String = 'string';
    case Int = 'int';
    case Float = 'float';
    case Bool = 'bool';
    case Uuid = 'uuid';
    case Datetime = 'datetime';
    case Date = 'date';
    case Duration = 'duration';
    case Enum = 'enum';
    case Json = 'json';
}
