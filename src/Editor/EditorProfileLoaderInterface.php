<?php

declare(strict_types=1);
namespace CoolMS\Core\Editor;

/**
 * Source of editor-profile definitions. The default implementation reads
 * YAML files from `config/modules/editor/profiles/` and the app-level
 * override at `config/profiles/editor/`; alternative implementations (DB,
 * remote API) are free to plug in by tagging themselves
 * `coolms.editor.profile_loader` and being delivered into the resolver
 * via the EditorContributorPass.
 *
 * Loaders return a flat map of name → raw profile descriptor. The
 * resolver applies inheritance and `+`/`-` directives once across the
 * merged set, so multiple loaders compose without each one knowing about
 * the others.
 */
interface EditorProfileLoaderInterface
{
    /**
     * @return array<string, RawProfileDescriptor>
     *
     * Map keyed by profile name; the descriptor carries the unparsed
     * extends / contributors / allowedWidgets fields so the resolver can
     * apply inheritance after every loader has contributed
     */
    public function load(): array;

    /**
     * Override priority. Loaders with higher priority replace lower-priority
     * loaders' definitions for the same profile name (mirrors the YAML
     * override pattern in NaviGraph + Form). Module-level loaders run at
     * priority 0; app-level overrides run at 100.
     */
    public function getPriority(): int;
}
