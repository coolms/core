<?php

declare(strict_types=1);
namespace CoolMS\Core\Web;

use CoolMS\CoreModule\Template\ContextContributorInterface;

/**
 * Web-scoped marker for SSR context contributors. The signature
 * lives on the neutral parent ({@see ContextContributorInterface});
 * the Web extension here exists so the SSR builder's tagged
 * iterator picks up only contributors that opted in to the Web
 * surface -- separate from the Document module's contributor set.
 *
 * Implementations stay tagged `coolms.template_context_contributor`
 * (auto-tagged in `Web\Infrastructure\DependencyInjection\Extension`).
 * Existing implementations (e.g. `PageBodyContextContributor`)
 * remain unchanged -- they already match the neutral signature.
 */
interface TemplateContextContributorInterface extends ContextContributorInterface
{
}
