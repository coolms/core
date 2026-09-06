<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\DataType;

/**
 * One `<output>` column on a DMN decision table (M3.1.c). Immutable.
 *
 * Mirrors {@see InputClauseAst} structurally but without the
 * resolving expression -- outputs are pure projection clauses
 * whose row values come from the per-rule output entries
 * (held on {@see RuleAst::$outputEntries}).
 *
 * **Name (alias) vs id**: in DMN 1.3 the `name` attribute on
 * `<output>` is the key under which the projected value lands in
 * the decision's output object when the table returns a structured
 * result. `$id` is the spec-required stable identifier (same role as
 * {@see InputClauseAst::$id}); `$name` is what cell-level priority
 * lists reference and what shows up in the evaluator's return map.
 * When `$name` is null the evaluator falls back to `$id` so the
 * output object always carries a usable key.
 */
final readonly class OutputClauseAst
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $label,
        public DataType $type,
    ) {
    }
}
