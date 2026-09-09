<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

use CoolMS\Core\Template\ContextContributorInterface;

/**
 * Document-scoped marker for contributors that enrich the
 * `DocumentInstance.context` at prepare-time (the JSON-friendly
 * shape persisted on the row and consumed by the async renderer).
 *
 * Mirrors Web's `TemplateContextContributorInterface` --
 * implementations satisfy the neutral parent's contract; the
 * marker exists so the Document module's tagged iterator picks
 * up only contributors that opted in to the Document surface,
 * independent of Web's SSR contributor set.
 *
 * Implementations are auto-tagged `coolms.document.context_contributor`
 * via the Document module's DI extension. Contributors MUST
 * return JSON-serializable arrays so the persisted context
 * round-trips cleanly through persistence and the message bus.
 */
interface DocumentContextContributorInterface extends ContextContributorInterface
{
}
