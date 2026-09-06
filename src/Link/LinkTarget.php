<?php

declare(strict_types=1);
namespace CoolMS\Core\Link;

/**
 * Wire-shape value object for a link reference. Carries the target type
 * (which selects the resolver) plus the identifier the resolver expects.
 *
 *   type        the per-target-type prefix surfaced in the dtmpl widget
 *               (`{widget:link:TYPE:IDENTIFIER}`). Six built-ins planned in
 *               the picker: 'page', 'section', 'taxonomy', 'vfs', 'url',
 *               'route'. Unknown types fall through the aggregator and
 *               produce a broken result -- the LinkResolver is responsible
 *               for never raising.
 *   identifier  resolver-specific identity. Per type:
 *                 page / section / taxonomy / vfs   -> UUID
 *                 url                                -> literal URL
 *                 route                              -> Symfony route name
 *               The format is not validated here; that's the resolver's
 *               concern (a malformed UUID is a broken link, not a fatal).
 *   params      free-form bag forwarded to the resolver -- used by the
 *               'route' type to pass URL parameters
 *               (`{id: 42, slug: 'about'}`), reserved for future axes on
 *               other types.
 */
final readonly class LinkTarget
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        public string $type,
        public string $identifier,
        public array $params = [],
    ) {
    }
}
