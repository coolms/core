<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use CoolMS\Core\Definition\Violations;
use Throwable;

use function count;
use function sprintf;

/**
 * Thrown by a per-module deploy-time validator when one or more
 * error-severity violations exist on a parsed definition. Carries
 * the full {@see Violations} collection so the deployer rolls back
 * the surrounding transaction and surfaces every problem at once
 * (collect-all UX -- one round trip exposes every fix).
 *
 * Per-module parse exceptions
 * (`BpmnLiteParseException` in the consuming application and
 * future DMN equivalents) extend this class -- a parse error appears
 * as a single Violation in the same collection, and editors render
 * one error list regardless of which pass produced the items.
 *
 * NOT `final` for the same reason -- subclasses customise the
 * exception name without rebuilding the Violations carriage.
 *
 * Renamed from `DefinitionValidationException` on 2026-06-01 when the
 * shared validation infrastructure was extracted from Workflow into
 * the Definition foundation tier.
 */
class DefinitionValidationException extends InvalidDefinitionException
{
    public function __construct(
        public readonly Violations $violations,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Definition is invalid: %d violation(s).', count($violations)),
            previous: $previous,
        );
    }

    public static function fromViolations(Violations $v): static
    {
        // late-static-binding keeps subclass type (e.g. BpmnLite...)
        // when subclasses call `fromViolations()` on themselves.
        // Subclasses that want a single-Violation convenience
        // constructor override their own factory.
        /* @phpstan-ignore-next-line new.staticInAbstractClassStaticMethod */
        return new static($v);
    }
}
