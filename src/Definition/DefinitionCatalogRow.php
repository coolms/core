<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use CoolMS\Core\Definition\DefinitionSource;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Uniform row shape returned by every
 * {@see DefinitionCatalogProviderInterface}. The unified Definitions
 * admin UI iterates a merged list of these across all registered
 * providers (Workflow, Decision, future Form) without caring which
 * concrete module produced any given row -- the `module` discriminator
 * lets the FE render module-specific pills + drill-down routes.
 *
 * **Why a single VO, not per-module DTOs**: the catalog page's job is
 * "show me everything deployed across modules in one list" -- a single
 * shape lets the FE render uniform columns. Per-module concerns
 * (BPMN-Lite vs DMN body display, format-specific validation rules)
 * live in the per-module Designer / viewer pages reached via row
 * click-through.
 *
 * Designed as a thin readonly VO -- every field has stable semantics
 * across modules. Optional fields stay `?type` rather than defaulting
 * to magic sentinels (NULL is the natural "no value here" carrier).
 */
final readonly class DefinitionCatalogRow
{
    public function __construct(
        /**
         * Module discriminator matching `AbstractDefinition::module()` --
         * `'workflow'`, `'decision'`, future `'form'`. Drives the
         * module pill on the FE + the drill-down route choice.
         */
        public string $module,

        /**
         * Definition row primary key. Used by the FE to compose
         * drill-down links and (future) version timeline lookups.
         */
        public Uuid $definitionId,

        /**
         * Stable definition key (dot-separated, e.g.
         * `identity.verify_new_user_spine`). Matches the BPMN/DMN
         * body's `process.id` / `decision.id` field.
         */
        public string $definitionKey,

        /**
         * Human-readable display name, set at definition creation +
         * mutable via the per-module admin endpoints. The FE uses
         * this as the primary column label; `definitionKey` is the
         * secondary technical hint.
         */
        public string $displayName,

        /**
         * The currently-active version number for this Definition.
         * `null` means "never deployed" -- typically a draft-only
         * Definition that was created in the Designer but never
         * pushed through deploy.
         */
        public ?int $latestVersion,

        /**
         * Source of the latest version row's body bytes per
         * {@see DefinitionSource}. `null` mirrors `latestVersion=null`
         * -- there's no version row to discriminate.
         */
        public ?DefinitionSource $latestVersionSource,

        /**
         * Whether the latest contributor-source version row carries
         * `moduleLock=true` (i.e. operator has Forked-to-VFS).
         * Always `null` when the latest source is `Vfs` or the
         * Definition has no version yet.
         */
        public ?bool $moduleLock,

        /**
         * Deploy timestamp of the latest version row. `null` for
         * never-deployed Definitions.
         */
        public ?DateTimeImmutable $deployedAt,

        /**
         * Identity user UUID who deployed the latest version. `null`
         * means "system" (installer / fixture / contributor branch
         * with no HTTP caller). The FE resolves to a display name via
         * the existing user-lookup endpoint.
         */
        public ?Uuid $deployedById,

        /**
         * Whether a draft row exists for this Definition. The FE
         * uses this to show a "draft pending" chip + route Designer
         * clicks straight into edit mode instead of read-only viewer.
         * `false` for contributor-source-only Definitions that have
         * never been forked.
         */
        public bool $hasDraft,

        /**
         * When the definition was retired (archived), or `null` while
         * it is active. Retired rows are hidden from the catalog by
         * default and only surface under `retired=true|all`; the FE
         * renders the timestamp on the "Retired" chip so an operator
         * can tell a recent archive from an ancient one.
         */
        public ?DateTimeImmutable $retiredAt = null,
    ) {
    }
}
