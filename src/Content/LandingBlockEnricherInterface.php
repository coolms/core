<?php

declare(strict_types=1);
namespace CoolMS\Core\Content;

/**
 * Fills a landing block with data it cannot store.
 *
 * !! **This exists because a contributed block type could be declared but not
 * drawn.** `BlockTypeRegistry` already lets any module add a block type, and
 * `LandingBlocksReader` cleans author input against that type's field schema --
 * but the schema is what an EDITOR types. A block that renders live state (a
 * deployed workflow, a stock level, a build status) needs data that is not in
 * the page and must not be, because storing it is how it goes stale.
 *
 * The template cannot fetch it either: DTMPL include paths and widget params
 * are both parsed statically, so a block partial cannot say "load the thing
 * this block names". The data has to arrive already attached to the block.
 *
 * !! Enrichers run AFTER cleaning, so they may add keys the field schema does
 * not declare. That is the point -- the schema governs what an author may
 * write, not what a block may carry -- but it also means an enricher can shadow
 * an authored field, so it must add its own keys rather than rewrite theirs.
 *
 * Implementations are tagged `coolms.content.landing_block_enricher` by
 * autoconfiguration; nothing else changes to add one.
 */
interface LandingBlockEnricherInterface
{
    /**
     * Does this enricher handle blocks of `$type`?
     */
    public function supports(string $type): bool;

    /**
     * Return the block with whatever live data it needs added.
     *
     * !! MUST NOT THROW. This runs while a public page is being assembled, so a
     * module whose backing state is missing or unreadable degrades that one
     * block -- it does not take the page down. Signal the failure inside the
     * returned block so the partial can render something honest, and log it.
     *
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    public function enrich(array $block): array;
}
