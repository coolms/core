<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Ship A Phase 2 -- discriminator on
 * `AbstractDefinitionVersion` in the consuming application
 * marking where the body bytes for this version come from.
 *
 * Lives at the Definition module level (not Workflow) so future
 * Decision-table and Form definition versions inherit the same
 * vocabulary. The split is binary:
 *
 *  - {@see self::Vfs}: the version's body lives in a versioned VFS
 *    Node (`/workflows/{key}/v{N}.bpmn.json`,
 *    `/decisions/{key}/v{N}.dmn.json`, etc.). The version row's
 *    `nodeId` points at it; reads route through the VFS-backed
 *    loader. Authors edit drafts via the Designer, deploy via the
 *    per-module deployer, and every subsequent version is minted as
 *    a fresh Node. This is the canonical author-driven path that
 *    M2.d/M2.n shipped.
 *
 *  - {@see self::Contributor}: the version's body comes from a
 *    module-owned {@see WorkflowDefinitionContributorInterface}
 *    (or the future sibling Decision/Form contributor interfaces).
 *    The version row's `nodeId` is NULL; reads route through the
 *    contributor registry. No draft, no Designer Save -- the body
 *    is whatever the bundled JSON resource shipped by the module
 *    author says it is. Used for spines like
 *    `identity.verify_new_user_spine` that ARE the platform and
 *    should NOT be edited by tenants.
 *
 * **Fork-to-VFS lifecycle** -- a contributor-source definition can
 * be "forked" by a tenant: the read-only viewer's `Fork to VFS`
 * action mints a new draft + VFS Node from the contributor's
 * current body, flips the active version pointer, and sets the
 * `moduleLock` flag on the row to true. After fork, future
 * contributor drift (a module upgrade ships a new body) is
 * IGNORED by the installer for that definition -- the operator
 * has taken ownership. A symmetric `Revert to module` action
 * drops the lock + the VFS-source versions and re-points the
 * active flag back at the latest contributor-source row.
 *
 * **Why a string-backed enum**, not int: Doctrine's enum mapping
 * surfaces the case name as the column value, so `vfs` / `contributor`
 * appear in DB tooling + log lines. Int-backed would have been
 * marginally smaller but unreadable in queries.
 *
 * **Why in `Domain/Install` instead of `Domain/Entity`** (Phase 3
 * move): the enum is part of the public contract shared with
 * cross-module contributor implementations
 * ({@see WorkflowDefinitionContributorInterface::getSource}). Living
 * alongside the contract namespace -- rather than the entity that
 * persists it -- keeps cross-module imports clean under the module-
 * boundary rule (`coolms.architecture.crossModuleDomainImport`,
 * which permits `Domain/Install` but not `Domain/Entity` imports).
 */
enum DefinitionSource: string
{
    case Vfs = 'vfs';
    case Contributor = 'contributor';
}
