<?php

declare(strict_types=1);
namespace CoolMS\Core\Content;

use CoolMS\Core\Space\SpaceProviderInterface;

/**
 * Page-flavoured marker for {@see SpaceProviderInterface} implementations.
 *
 * The sibling of {@see ArticleSpaceProviderInterface}, and the "second
 * space-backed surface" its docblock predicted — which is why the article
 * tag was namespaced `article_space_provider` rather than the bare
 * `space_provider` that would now be ambiguous. Same reasoning applies
 * here: implementors are tagged `coolms.content.page_space_provider`, so
 * the two registries never collect each other's providers.
 *
 * Carries no contract of its own; it exists purely as the autoconfigure
 * target. Implementations are collected by
 * `PageSpaceRegistry` in the consuming application.
 *
 * Providers MAY return an empty list when no spaces apply for the given
 * user. Providers MUST NOT throw on permission denials — they should
 * silently skip the affected entry.
 *
 * This is the surface that survives: an article and a page
 * are the same Package (`application/vnd.coolms.page`), so page spaces
 * are what the admin browses and article spaces retire with the Articles
 * slice.
 */
interface PageSpaceProviderInterface extends SpaceProviderInterface
{
}
