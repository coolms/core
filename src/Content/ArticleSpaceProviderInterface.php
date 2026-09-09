<?php

declare(strict_types=1);
namespace CoolMS\Core\Content;

use CoolMS\Core\Space\SpaceProviderInterface;

/**
 * Article-flavoured marker for {@see SpaceProviderInterface} implementations.
 *
 * Carries no contract of its own -- it exists purely as the autoconfigure
 * target so the Content DI extension can tag implementors
 * `coolms.content.article_space_provider` without sweeping every generic
 * `SpaceProviderInterface` in the container (which would also catch Media
 * and Document providers and double-tag).
 *
 * The tag is namespaced `article_space_provider` rather than
 * `space_provider` deliberately: Content may later expose a second
 * space-backed surface (pages), and a bare `coolms.content.space_provider`
 * would then be ambiguous.
 *
 * Implementations are collected by
 * `ArticleSpaceRegistry` in the consuming application.
 *
 * Providers MAY return an empty list when no spaces apply for the given
 * user. Providers MUST NOT throw on permission denials -- they should
 * silently skip the affected entry.
 */
interface ArticleSpaceProviderInterface extends SpaceProviderInterface
{
}
