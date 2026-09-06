<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Carries an expression-language source fragment as data — never
 * evaluated by the AST or validator. The engine (M2.f+) hands the
 * `expression` string to `ExpressionService` at token-advance time;
 * the deploy-time validator only invokes `ExpressionService::lint()`
 * for syntax checks (see `docs/investigations/m2c-design.md` §5.5(f)).
 *
 * M2 ships exactly one expression language. The parser canonicalises
 * the declared `language` to `'EL'` regardless of original case; rule
 * `WF.EL_LANGUAGE` rejects any other value at validate time.
 */
final readonly class ConditionExpression
{
    public function __construct(
        public string $expression,
        public string $language = 'EL',
    ) {
    }
}
