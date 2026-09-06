<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use Symfony\Component\Uid\Uuid;

use function sprintf;

/**
 * Raised by `DecisionEvaluator` in the consuming application
 * when the target `DecisionDefinition` in the consuming application
 * carries no `latestVersionId` -- author created the Definition (so
 * the M3.1.b row + the M3.1.b draft exist) but never deployed.
 *
 * Distinct shape from "Definition row doesn't exist at all" (that
 * raises an upstream lookup error before this point) and from
 * "deploy failed mid-flight" (the M3.1.e deployer's atomic tx means
 * a failed deploy doesn't advance the pointer, so the symptom is
 * exactly the same as "never deployed" -- which is correct, both
 * cases are "no deployable version exists right now").
 *
 * Mirrors the philosophy of the Workflow side's
 * `DraftMissingException`: a typed deploy-state guard the engine
 * surfaces before doing any VFS I/O.
 */
final class DecisionNotDeployedException extends DecisionEvaluationException
{
    public static function forDefinition(Uuid $definitionId, string $definitionKey): self
    {
        return new self(sprintf(
            'DecisionDefinition "%s" (id %s) has no deployed version; nothing to evaluate. Call DecisionDeployer::deploy() first.',
            $definitionKey,
            $definitionId->toRfc4122(),
        ));
    }
}
