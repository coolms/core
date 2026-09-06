<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\DataType;

/**
 * One `<input>` column on a DMN decision table (M3.1.c). Immutable.
 *
 * **Cell-content split**: the `$expression` field carries the
 * input-resolving expression text -- typically a variable reference
 * like `applicant.age` or `variables.input.income` -- which the
 * M3.1.f evaluator passes through the M1.4 EL service against the
 * process variables provided at evaluation time. This is the value
 * being tested; the per-rule input *entries* (held on
 * {@see RuleAst::$inputEntries}) are the unary tests applied to it.
 *
 * **Type coercion**: the evaluator uses `$type` to coerce the
 * EL-evaluated input value before unary-test matching. Setting
 * `$type` to {@see DataType::Any} disables coercion -- useful when
 * the source variable's type is unknown to the author or genuinely
 * polymorphic.
 *
 * **Label vs id**: `$id` is the spec-required stable identifier
 * (rule entries reference it implicitly by column position; the id
 * is for cockpit hover-text + future column-renaming refactors).
 * `$label` is the human-friendly column header rendered by the M3.2
 * BPMN-editor's DMN sibling view.
 */
final readonly class InputClauseAst
{
    public function __construct(
        public string $id,
        public ?string $label,
        /**
         * Expression text evaluated against process variables to
         * produce the value tested by per-rule unary tests. Stored
         * raw; the M3.1.f evaluator pipes it through the M1.4 EL
         * service (`ExpressionService`).
         */
        public string $expression,
        public DataType $type,
    ) {
    }
}
