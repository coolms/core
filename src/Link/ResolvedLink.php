<?php

declare(strict_types=1);
namespace CoolMS\Core\Link;

/**
 * Outcome of `LinkResolverInterface::resolve()`. Carries the rendered URL
 * plus metadata the dtmpl renderer needs to emit `<a href=…>` markup.
 *
 *   href      final user-facing URL ('/about', 'https://example.com').
 *             Empty string when the target couldn't be resolved (paired
 *             with `broken: true`); renderers must never serialise an
 *             `<a href="">` for a broken result.
 *   label     default anchor text — page title, asset filename, route
 *             label. Null when the resolver can't supply one (e.g. for
 *             literal URLs); the renderer's own `label="…"` widget
 *             param wins anyway, this is just the fallback for empty
 *             cached labels.
 *   external  true for absolute http(s) URLs. Renderers typically
 *             default `target="_blank" rel="noopener"` for external
 *             links unless the widget param overrides.
 *   broken    true when resolution failed: target deleted, malformed
 *             identifier, or no resolver claimed the type. The renderer
 *             applies the configured broken-link fallback (default:
 *             visible warning).
 *   published true when the target is publicly visible. False for draft
 *             pages or otherwise unpublished targets that the resolver
 *             could still locate. Distinct from `broken`: an unpublished
 *             page is found and has a URL, but a public render context
 *             should treat it as broken-equivalent while an admin
 *             preview can render it as a "draft" anchor. Resolvers that
 *             have no notion of publication state leave this true.
 */
final readonly class ResolvedLink
{
    public function __construct(
        public string $href,
        public ?string $label = null,
        public bool $external = false,
        public bool $broken = false,
        public bool $published = true,
    ) {
    }

    /**
     * Sentinel "couldn't resolve" result. The aggregator returns this
     * when no tagged resolver supports the target type, or when the
     * supporting resolver returned null. The $type / $identifier
     * arguments are accepted (and currently unused) so future logging
     * or renderer fallback can surface which target the broken link
     * pointed to without changing the call sites.
     */
    public static function broken(string $type, string $identifier): self
    {
        // $type and $identifier are intentionally available to the
        // factory but not stored on the returned VO — the renderer
        // already has access to the original LinkTarget when it wants
        // to format a "broken: page:UUID" diagnostic. Holding them on
        // ResolvedLink would leak resolver-internal context into a
        // shape that's also returned for non-broken cases.
        unset($type, $identifier);

        return new self(href: '', label: null, external: false, broken: true, published: false);
    }
}
