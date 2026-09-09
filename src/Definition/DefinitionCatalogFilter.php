<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use CoolMS\Core\Definition\DefinitionSource;

/**
 * Readonly filter VO threaded from the API provider down to each
 * {@see DefinitionCatalogProviderInterface}. Per-provider
 * implementations MAY push these into SQL for efficiency; the API
 * provider re-applies them on the merged result for correctness.
 *
 * All fields are optional -- `null` means "don't filter on this".
 * The filter is the only input the API provider passes downstream;
 * per-module filter extensions (e.g. workflow-specific section
 * filter) stay on the per-module list endpoints, not this catalog.
 *
 * **Why a VO, not separate method params**: future filter additions
 * (status, owner, body-hash search) stay backward-compatible because
 * adding a property to the constructor doesn't break existing
 * implementations that ignore it. Per-module providers that only
 * support a subset of filters can branch on `$filter->module` etc.
 * without breaking the contract.
 */
final readonly class DefinitionCatalogFilter
{
    public function __construct(
        /**
         * Restrict to one module's rows. `null` = all modules.
         * Implementations MAY exit early when `$filter->module` is
         * set and doesn't match `$this->getModule()` -- the API
         * provider already skips non-matching providers but a defensive
         * check inside provide() prevents accidental cross-talk.
         *
         * Kept single-valued: it is the provider short-circuit hint, and
         * a provider serves exactly one module. To filter on SEVERAL
         * modules use {@see $modules} -- the registry then cannot skip
         * providers, which is correct, because more than one applies.
         */
        public ?string $module = null,

        /**
         * Restrict by latest-version source. `null` = both.
         */
        public ?DefinitionSource $source = null,

        /**
         * Restrict to forked-from-module Definitions only.
         * `null` = no filter, `true` = only locked,
         * `false` = only unlocked. The catalog page uses this for the
         * "show forks" chip.
         */
        public ?bool $moduleLock = null,

        /**
         * Free-text search on `definitionKey` + `displayName`.
         * Implementations MAY ignore -- the API provider applies the
         * filter on the merged result as a safety net.
         */
        public ?string $search = null,

        /**
         * Retirement visibility. **The default is `false`, not `null`**
         * -- unlike every other field here, "don't filter" is NOT the
         * safe default: a retired definition is one an operator
         * deliberately archived, so leaving it in the default list
         * would make retiring a no-op from the UI's point of view.
         *
         * `false` = active only (default), `true` = retired only,
         * `null` = both (the explicit `retired=all` opt-in).
         */
        public ?bool $retired = false,

        /**
         * Restrict to ANY OF these modules (OR). Empty = no filter.
         *
         * Exists because the admin grid's Module filter is a MULTI-select
         * ("workflow OR decision"), which single-valued {@see $module}
         * cannot express. Both may be set; a row must satisfy both, so
         * callers normally set one or the other.
         *
         * @var list<string>
         */
        public array $modules = [],

        /**
         * Restrict to ANY OF these sources (OR). Empty = no filter.
         * The multi-select sibling of {@see $source}, same rationale.
         *
         * @var list<DefinitionSource>
         */
        public array $sources = [],

        /**
         * Case-insensitive substring on `definitionKey` ALONE.
         *
         * Distinct from {@see $search}, which spans key + display name:
         * the grid filters those two columns INDEPENDENTLY, and folding
         * them into one needle makes a Key filter match rows whose key
         * doesn't contain it (the display name did). Both may be set and
         * are ANDed.
         */
        public ?string $definitionKey = null,

        /** Case-insensitive substring on `displayName` alone. Sibling of {@see $definitionKey}. */
        public ?string $displayName = null,

        /**
         * Column to sort the merged result by -- one of `module`,
         * `displayName`, `definitionKey`, `latestVersion`,
         * `latestVersionSource`, `deployedAt`, `retiredAt`. `null` keeps
         * the stable cross-provider default (`displayName` ASC).
         *
         * Sorting MUST happen here rather than in the browser: the client
         * only holds one page, so a client-side sort silently reorders
         * that page instead of the result set -- the same class of bug as
         * client-side filtering.
         */
        public ?string $sort = null,

        /** `true` sorts descending. Ignored when {@see $sort} is null. */
        public bool $sortDescending = false,
    ) {
    }
}
