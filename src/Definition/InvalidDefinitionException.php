<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use DomainException;

/**
 * Raised when a concrete Definition aggregate
 * (e.g. `WorkflowDefinition` in the consuming application,
 * `WorkflowDraftVersion` in the consuming application,
 * `WorkflowDefinitionVersion` in the consuming application, or
 * a future DMN equivalent) is constructed or mutated with an invalid
 * argument shape (bad definition key, blank display name,
 * non-monotonic version number, etc.).
 *
 * Engine-level errors (parse failures, runtime token-advance
 * problems) live in their own exception classes; this one is for
 * pure aggregate invariants caught at construct / mutator time.
 *
 * NOT `final` -- {@see DefinitionValidationException} extends this
 * class so the deployer can catch a single base type for both
 * aggregate-invariant breaches and validator rule failures, and the
 * per-module parse exceptions
 * (`BpmnLiteParseException` in the consuming application, etc.)
 * extend {@see DefinitionValidationException} to fold parse errors
 * into the same exception ladder.
 */
class InvalidDefinitionException extends DomainException
{
}
