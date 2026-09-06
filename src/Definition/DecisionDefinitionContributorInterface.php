<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Module-owned contributor that ships a DMN 1.3 XML decision-table
 * body along with the module that authored it (M3.1.g).
 *
 * Sibling to {@see WorkflowDefinitionContributorInterface}: same
 * shape, different format (DMN XML instead of BPMN-Lite JSON).
 * Sits in {@see \App\Definition} (level 1) rather than
 * {@see \App\Decision} (level 1) so that ANY module -- including the
 * foundation tier (Identity, VFS, Form) -- can implement the contract
 * without breaking the module-boundary rule. The Decision module's
 * installer iterates every tagged implementation at boot/install
 * time and deploys the bodies through
 * `DecisionDeployer` in the consuming application.
 *
 * **Why this lives where it lives (read this before extracting):**
 *
 * The monorepo will eventually split into independent packages.
 * In that future:
 *  - `core/identity` ships, say, an identity-scoring DMN body + this
 *    contributor implementation. Identity is the package that
 *    knows what the scoring decision means.
 *  - `core/decision` ships the engine + deployer. It knows how to
 *    deploy any DMN body but has zero knowledge of specific tables.
 *  - `core/definition` ships this interface + the entity foundation
 *    (Definition, DraftVersion, DefinitionVersion) -- shared between
 *    Workflow and Decision.
 *
 * That cleanly separates business-rule ownership (per-module) from
 * deploy infrastructure (Decision). Mirrors the M2.n architectural
 * refactor that introduced the Workflow contributor pattern (ledger
 * #622-625).
 *
 * Auto-discovered via Symfony DI: any concrete implementation in
 * an `App\` autoconfigure-enabled namespace gets the
 * `coolms.decision.definition_contributor` tag automatically.
 */
interface DecisionDefinitionContributorInterface
{
    /**
     * Stable, dot-separated key matching the DMN body's
     * `<decision id="...">` attribute. Used by the Decision deployer
     * to:
     *  - look up an existing `DecisionDefinition` in the consuming application
     *    row (idempotency),
     *  - mint the `/decisions/{key}/` VFS subdirectory,
     *  - reject deploys when the body's decision id doesn't match
     *    this key (`DecisionIdMismatchException` in the consuming application).
     *
     * Convention: dotted-namespace form like `pricing.discount`,
     * `risk.score`, `support.priority`. Same shape as
     * {@see WorkflowDefinitionContributorInterface::getDefinitionKey()}.
     */
    public function getDefinitionKey(): string;

    /**
     * Human-readable display name for cockpit + deploy log lines.
     * Not interpreted by the engine; pure metadata.
     */
    public function getName(): string;

    /**
     * The DMN 1.3 XML body to deploy as the initial draft. The
     * deployer pipes this through the M3.1.c parser + M3.1.d
     * validator before persisting; malformed bodies fail loudly
     * during install.
     *
     * Implementations typically `file_get_contents()` a resource
     * shipped alongside the module's PHP source. Return value should
     * be the raw XML string (not a parsed DOMDocument or AST).
     */
    public function getBody(): string;
}
