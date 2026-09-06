<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Definition\InvalidDefinitionException;
use Symfony\Component\Uid\Uuid;

use function sprintf;

/**
 * Raised by `DecisionDeployer` in the consuming application
 * when a `DecisionDefinition` in the consuming application has no
 * companion `DecisionDraftVersion` in the consuming application row
 * at deploy time.
 *
 * "Exactly one draft row per Definition" is the invariant
 * (`uq_decision_draft_definition`). A missing draft means upstream
 * code created the Definition row but never minted the draft -- the
 * deploy can't proceed because there's nothing to copy bytes from.
 *
 * Extends {@see InvalidDefinitionException} so callers that catch the
 * Definition module's base type ("aggregate state isn't deploy-ready")
 * pick this up uniformly with display-name / version-counter
 * invariants.
 *
 * Mirrors `DraftMissingException` in the consuming application.
 */
final class DraftMissingException extends InvalidDefinitionException
{
    public static function forDefinition(Uuid $definitionId, string $definitionKey): self
    {
        return new self(sprintf(
            'No DecisionDraftVersion row found for DecisionDefinition "%s" (id %s); a draft must exist before deploy.',
            $definitionKey,
            $definitionId->toRfc4122(),
        ));
    }
}
