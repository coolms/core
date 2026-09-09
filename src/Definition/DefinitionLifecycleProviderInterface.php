<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * WRITE-side counterpart to
 * {@see \CoolMS\Core\Definition\DefinitionCatalogProviderInterface},
 * implemented by each Definition consumer module (Workflow, Decision,
 * future Form).
 *
 * **Why a separate seam and not more methods on the catalog provider**:
 * the catalog contract explicitly declares its implementations
 * "lightweight readers -- no business logic, no side effects, no event
 * dispatching" and the catalog UI "a read-only surface". Retiring and
 * deleting are side effects with guards and events, so they get their
 * own seam rather than eroding that contract. Same dependency
 * direction: the interface lives at the Definition foundation tier
 * (L1), consumers in L3 implement it.
 *
 * **Why a shared seam at all**: without one, each module grows its own
 * subtly different delete semantics -- exactly how the contributor /
 * fork apparatus ended up existing for Workflow only. One contract, one
 * set of rules, per-module guards.
 *
 * **Tag**: `coolms.definition.lifecycle_provider`, wrapped in
 * `Closure(): DefinitionLifecycleProviderInterface` factories by the
 * compiler pass (lazy registry), so cold paths never pay for a
 * database connection.
 */
interface DefinitionLifecycleProviderInterface
{
    /**
     * Module discriminator this provider owns (`'workflow'`,
     * `'decision'`, ...). Matches
     * `AbstractDefinition::module()` in the consuming application
     * so the registry can route by the catalog row's `module` column.
     */
    public function getModule(): string;

    /**
     * Retire (archive) the definition: hide it from the active catalog
     * and stop new work starting against it, while leaving deployed
     * version history AND any live instances untouched.
     *
     * Idempotent -- retiring an already-retired definition is a no-op
     * that preserves the original timestamp.
     *
     * @throws DefinitionLifecycleRefused when this definition may not be retired
     *                                    (e.g. module-shipped: the installer would
     *                                    simply re-create it, so retiring is a lie)
     * @throws DefinitionNotFound         when the key is unknown to this module
     */
    public function retire(string $definitionKey): void;

    /** Bring a retired definition back into the active catalog. */
    public function unretire(string $definitionKey): void;

    /**
     * PERMANENTLY delete a definition, its draft and its row.
     *
     * **Only legal when the definition has NEVER been deployed**
     * (`latestVersionId === null`). Deployed version bodies are
     * immutable by construction -- their VFS Nodes are chmod-clamped to
     * 0o444 at deploy -- and running instances resolve their AST through
     * the definition, so anything that has shipped is retired instead.
     * Implementations MUST refuse rather than cascade.
     *
     * @throws DefinitionLifecycleRefused when the definition has deployed history,
     *                                    live instances, or is module-shipped
     * @throws DefinitionNotFound         when the key is unknown to this module
     */
    public function delete(string $definitionKey): void;
}
