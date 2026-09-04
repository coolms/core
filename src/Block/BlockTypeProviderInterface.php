<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * Tagged service contract for contributing landing-page section block types.
 *
 * Any module can ship its own landing blocks -- a Commerce module's
 * `product-grid`, a Calendar module's `upcoming-events` -- by tagging an
 * implementation `coolms.content.block_type_provider`. The application
 * registers every provider and aggregates the result; the built-in set is one
 * such provider and has no privileged path. So neither the read-time normalizer
 * nor the editor-palette endpoint changes when a type is added.
 *
 * ⚠️ This contract lives in the package rather than in the application for one
 * reason: a module cannot contribute a block while the vocabulary for declaring
 * one belongs to another module. Published at one end only, the seam does not
 * exist.
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
