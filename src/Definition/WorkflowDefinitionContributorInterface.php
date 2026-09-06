<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Module-owned contributor that ships a BPMN-Lite JSON workflow
 * definition body along with the module that authored it.
 *
 * Sits in {@see \App\Definition} (level 1) rather than
 * {@see \App\Workflow} (level 3) so that ANY module -- including
 * the foundation tier (Identity, VFS, Form) -- can implement the
 * contract without breaking the module-boundary rule. The Workflow
 * module's installer iterates every tagged implementation at
 * boot/install time and deploys the bodies through
 * `WorkflowDeployer` in the consuming application.
 *
 * **Why this lives where it lives (read this before extracting):**
 *
 * The monorepo will eventually split into independent packages.
 * In that future:
 *  - `core/identity` ships its own verification BPMN body + this
 *    contributor implementation. Identity is the package that
 *    knows what the verification workflow means.
 *  - `core/workflow` ships the engine + deployer. It knows how to
 *    deploy any BPMN body but has zero knowledge of specific
 *    flows.
 *  - `core/definition` ships this interface + the entity
 *    foundation (Definition, DraftVersion, DefinitionVersion).
 *
 * That cleanly separates business-process ownership (Identity) from
 * deploy infrastructure (Workflow). Future Decision-tables (M3) +
 * Form Builder (M3+) will follow the same pattern with their own
 * `Decision*` and `Form*` contributor interfaces alongside this one.
 *
 * Identity's verification spine (M2.n) is the first consumer:
 * `IdentityVerificationWorkflowContributor` in the consuming application.
 *
 * Auto-discovered via Symfony DI: any concrete implementation in
 * an `App\` autoconfigure-enabled namespace gets the
 * `coolms.workflow.definition_contributor` tag automatically.
 */
interface WorkflowDefinitionContributorInterface
{
    /**
     * Stable, dot-separated key matching the BPMN body's
     * `process.id` field. Used by the Workflow deployer to:
     *  - look up an existing `WorkflowDefinition` in the consuming application
     *    row (idempotency),
     *  - mint the `/workflows/{key}/` VFS subdirectory,
     *  - reject deploys when the body's `process.id` doesn't
     *    match this key (ProcessIdMismatchException).
     *
     * Convention: dotted-namespace form like `identity.verify_new_user`,
     * `commerce.order_fulfilment`, `support.ticket_lifecycle`.
     */
    public function getDefinitionKey(): string;

    /**
     * Human-readable display name for cockpit + deploy log lines.
     * Not interpreted by the engine; pure metadata.
     */
    public function getName(): string;

    /**
     * The BPMN-Lite JSON body to deploy as the initial draft. The
     * deployer pipes this through the M2.c parser + validator before
     * persisting; malformed bodies fail loudly during install.
     *
     * Implementations typically `file_get_contents()` a resource
     * shipped alongside the module's PHP source. Return value should
     * be the raw JSON string (not a decoded array).
     */
    public function getBody(): string;

    /**
     * Tells the Workflow installer (Ship A Phase 4) which lifecycle to
     * use for this contributor's body.
     *
     *  - {@see DefinitionSource::Vfs} (default semantics for existing
     *    contributors): the installer writes the body to a `/workflows/
     *    {key}/draft.bpmn.json` Node, mints a draft + initial deploy,
     *    and from that point on the body is editable through the
     *    Designer like any other VFS-source definition. The
     *    contributor's `getBody()` is only consulted on install /
     *    first-deploy.
     *  - {@see DefinitionSource::Contributor}: the body stays in the
     *    bundled module resource forever (or until a user explicitly
     *    Forks-to-VFS per Phase 5). The installer skips VFS dir + draft
     *    creation entirely and stamps the version row with
     *    `nodeId=null, source=Contributor`. Every read at engine /
     *    cockpit time routes back through `getBody()` via the
     *    `ContributorDefinitionBodyLoader` in the consuming application.
     *    Idempotency / drift detection is by body hash (Phase 4).
     *
     * Existing contributors (Identity's verify-new-user spine, etc.)
     * default to {@see DefinitionSource::Vfs} -- behaviour-preserving.
     * New contributors that ship a "frozen" baseline workflow (e.g. a
     * platform-supplied audit pipeline) opt into Contributor.
     */
    public function getSource(): DefinitionSource;
}
