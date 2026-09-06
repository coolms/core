<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use DomainException;
use Throwable;

/**
 * Raised when a {@see \CoolMS\Core\Workflow\ProcessDefinitionAst}
 * or one of its child AST value objects is constructed in a shape
 * that violates a parse-time invariant (e.g. empty process id,
 * `element(id)` / `flow(id)` lookups against an unknown id).
 *
 * Distinct from {@see DefinitionValidationException}: this is for the
 * pure value-object self-checks performed inside the AST constructors
 * themselves — the kind of footgun you can hit only by hand-building
 * an AST (or feeding the parser a wildly malformed tree). The
 * validator's deploy-time rule violations live in `Violations` and
 * surface via `DefinitionValidationException`.
 *
 * The constructor carries an optional {@see SourceLocation} which —
 * once the parser's source-location tracking sidecar lands — will let
 * the deployer surface line/column hints. The parser currently passes
 * `null` everywhere (locked decision §10 Q3 deferred).
 */
final class AstViolationException extends DomainException
{
    public function __construct(
        string $message,
        public readonly ?SourceLocation $sourceLocation = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
