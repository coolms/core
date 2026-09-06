<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use RuntimeException;

/**
 * Root exception for runtime evaluation failures raised by the
 * `DecisionEvaluator` in the consuming application
 * and its hit-policy strategies. Distinct from the parse/validate
 * exception hierarchy ({@see DmnParseException} extends
 * `DefinitionValidationException`) because evaluation runs against
 * an already-deployed Definition -- failures here are runtime
 * input/data issues, not authoring errors.
 *
 * The `dmn:evaluate` service-task handler catches this and
 * re-wraps as a `ServiceTaskHandlerException` so engine token
 * advance can decide whether to fail the instance or route through
 * a boundary error event (M4+).
 *
 * Specific failure shapes are own-subclassed
 * ({@see UniqueHitPolicyViolatedException},
 * {@see AnyHitPolicyConflictException},
 * {@see DecisionNotDeployedException}); plain `DecisionEvaluationException`
 * is for catch-all runtime issues (EL syntax in a cell, type-coercion
 * failure, unknown aggregator collision, etc.).
 */
class DecisionEvaluationException extends RuntimeException
{
}
