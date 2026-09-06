<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Catalog contract implemented by each Definition consumer module
 * (Workflow, Decision, future Form). The unified Definitions admin
 * page reads from every registered provider, merges the results, and
 * presents them as one sortable / filterable table.
 *
 * **Why this lives in `Definition\Domain\Catalog`**: the contract is
 * cross-module by design — consumers in level 3 (Workflow, Decision)
 * implement it, the admin UI in a future `Definitions` module
 * consumes it. Living at the Definition foundation tier (level 1)
 * keeps the dependency direction correct: L3 implements L1's
 * interface, never the other way round.
 *
 * **Empty-state semantics**: when zero providers are registered (e.g.
 * a fresh install where Workflow + Decision modules aren't installed
 * yet), the catalog page renders an empty state with install CTAs.
 * Mirrors the Document + format-provider pattern (no Word/Pdf
 * installed → empty document library + install hints).
 *
 * **Lazy registry per ADR-118**: providers are wrapped in
 * `Closure(): DefinitionCatalogProviderInterface` factories by the
 * compiler pass. Cold start (`coolms:install`, container debug) skips
 * the Doctrine connection setup unless the admin page actually
 * needs the data.
 *
 * **Tag**: `coolms.definition.catalog_provider`. Auto-applied via
 * `registerForAutoconfiguration` in the Definition module's
 * Extension (when that gets set up; for now Workflow + Decision
 * extensions tag manually).
 *
 * Implementations should be lightweight readers — no business logic,
 * no side effects, no event dispatching. The catalog UI is a
 * read-only surface; per-module write paths stay on the per-module
 * admin endpoints (Designer Save/Deploy, Fork/Revert CLI, etc).
 */
interface DefinitionCatalogProviderInterface
{
    /**
     * Returns every Definition this provider manages, as a stream
     * of {@see DefinitionCatalogRow} VOs. Order is per-provider
     * choice; the catalog merge step in the API resource provider
     * applies the requested sort across the unified result.
     *
     * **Filtering**: implementations MAY apply the filter to push
     * `module=workflow` etc. down to SQL (avoiding loading rows
     * the caller will discard). Implementations MAY ignore the
     * filter and return everything — the API provider applies the
     * filter to the merged result regardless, so correctness is
     * preserved either way; this is a pure optimisation hint.
     *
     * @return iterable<DefinitionCatalogRow>
     */
    public function provide(DefinitionCatalogFilter $filter): iterable;

    /**
     * The module discriminator this provider's rows carry. Used by
     * the API provider to push module filters down to the matching
     * provider only — e.g. `?module=workflow` skips invoking the
     * Decision provider entirely. Matches
     * `AbstractDefinition::module()` for the corresponding concrete
     * Definition class.
     */
    public function getModule(): string;
}
