<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

use CoolMS\CoreModule\Template\ContextContributorInterface;

/**
 * Marker for Document-module contributors that run at **render
 * time** rather than prepare time, under the two-pipeline
 * model. Render-time contributors fire inside
 * `DocumentInstanceService::executeRender()` just before the
 * renderer consumes the context; their output does NOT persist on
 * `DocumentInstance.context`. Each render re-runs them, so entity
 * refs always reflect the latest entity state.
 *
 * Implementations are auto-tagged `coolms.document.render_context_contributor`
 * via the Document module's DI extension. The default render-time
 * contributor is `EntityHydratingContributor`.
 *
 * Distinction from {@see DocumentContextContributorInterface}:
 *  - This interface (render-time)   -> re-runs every render, output ephemeral.
 *  - DocumentContextContributorInterface (prepare-time) -> runs once at
 *    `prepare()`, output persisted to the row.
 */
interface DocumentRenderContextContributorInterface extends ContextContributorInterface
{
}
