<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

/**
 * One `<rule>` row on a DMN decision table (M3.1.c). Immutable.
 *
 * **Arity invariant** (enforced at parse time by
 * `DmnXmlParser` in the consuming application): the row
 * MUST carry exactly as many `inputEntries` as the table declares
 * `<input>` columns, and exactly as many `outputEntries` as the
 * table declares `<output>` columns. Parse fails with
 * `DMN.RULE_ARITY_MISMATCH` otherwise -- author tooling should
 * never let this happen but DMN-by-hand exports occasionally do.
 *
 * **Cell content** (per-cell, stored as raw text):
 *  - **inputEntries** are unary tests (DMN-spec "S-FEEL unary test")
 *    -- examples: `>= 18`, `< 100`, `[1..10]`, `"high"`, `not(0)`.
 *    M3.1 wires EL-evaluated tests via a small `UnaryTestParser`
 *    in the M3.1.f evaluator; raw strings here so future ships can
 *    swap to FEEL or extend the test grammar without an AST flip.
 *  - **outputEntries** are EL expressions evaluated against the
 *    process variables when the rule matches. Per the roadmap the
 *    M3.1 evaluator uses EL (not FEEL) so each entry is a Symfony
 *    Expression Language literal/expression.
 *
 * `$description` is optional cockpit metadata; the evaluator ignores
 * it. Pulled from `<description>` child of `<rule>` if present.
 */
final readonly class RuleAst
{
    /**
     * @param list<string> $inputEntries  raw unary-test strings, one per input column, in document order
     * @param list<string> $outputEntries raw EL expression strings, one per output column, in document order
     */
    public function __construct(
        public string $id,
        public array $inputEntries,
        public array $outputEntries,
        public ?string $description = null,
    ) {
    }
}
