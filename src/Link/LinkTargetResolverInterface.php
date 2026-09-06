<?php

declare(strict_types=1);
namespace CoolMS\Core\Link;

use CoolMS\Core\Link\LinkTarget;
use CoolMS\Core\Link\ResolvedLink;

/**
 * Implemented by any module that resolves a single link target type.
 *
 * Per-type resolvers are auto-tagged 'coolms.link.target_resolver' via
 * the Link module's autoconfiguration and aggregated into LinkResolver
 * through the standard tagged-iterator pattern. The aggregator walks the
 * iterable and dispatches to the first resolver that claims the type via
 * `supports($target->type)`.
 *
 * Built-in resolvers planned for sub-prompt A2:
 *   - PageLinkResolver       -> {widget:link:page:UUID}
 *   - SectionLinkResolver    -> {widget:link:section:UUID}
 *   - TaxonomyLinkResolver   -> {widget:link:taxonomy:UUID}
 *   - VfsLinkResolver        -> {widget:link:vfs:UUID}
 *   - UrlLinkResolver        -> {widget:link:url:LITERAL}
 *   - RouteLinkResolver      -> {widget:link:route:NAME}
 *
 * Resolvers MUST be side-effect-free -- the aggregator may invoke them
 * speculatively and the dtmpl renderer may resolve every link in a page
 * once per render. No DB writes, no eager fetches beyond what the
 * resolver needs to produce the URL.
 */
interface LinkTargetResolverInterface
{
    /**
     * True when this resolver handles the given target type. Match
     * exactly one type per resolver to keep the dispatch deterministic
     * (the aggregator returns the first match's result).
     */
    public function supports(string $type): bool;

    /**
     * Produce a ResolvedLink for the target. Return null when the
     * resolver claims the type but cannot produce a URL for the given
     * identifier -- the aggregator interprets null as "broken".
     *
     * Implementations should also return a ResolvedLink with
     * `broken: true` rather than throwing for soft errors (target
     * deleted, draft page on public surface, malformed UUID); reserve
     * exceptions for genuinely fatal misconfiguration.
     */
    public function resolve(LinkTarget $target): ?ResolvedLink;
}
