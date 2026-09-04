<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * Tagged service contract for contributing landing-page section block types
 * (ADR-130, W5.j).
 *
 * Any module can ship its own landing blocks (e.g. a Commerce module's
 * `product-grid`, a Calendar module's `upcoming-events`) by tagging an
 * implementation `coolms.content.block_type_provider` — the built-in catalog
 * is itself just one such provider ({@see \App\Content\Application\Service\BuiltinBlockTypeProvider}).
 * {@see \App\Content\Application\Service\BlockTypeRegistry} aggregates them, so
 * neither the read-time reader nor the editor-palette endpoint changes when a
 * type is added.
 *
 * Providers MUST return block types whose ids are stable + URL-safe; the
 * registry de-dupes on id (higher {@see self::priority()} wins).
 */
interface BlockTypeProviderInterface
{
    /**
     * The block types this provider contributes, in palette order.
     *
     * @return list<BlockType>
     */
    public function blockTypes(): array;

    /**
     * Merge order: higher-priority providers are applied first, so when two
     * providers declare the same block-type id the higher-priority one wins.
     * Built-in types use 100 — module contributors (default 0) can ADD new
     * types but never silently override a built-in.
     */
    public function priority(): int;
}
