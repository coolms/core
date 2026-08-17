<?php

declare(strict_types=1);

namespace CoolMS\Core\Retention;

/**
 * A module-published seam for a retention sweep: something that deletes its own
 * expired/spent rows from an unbounded-growth table. Implementers are collected
 * (tag `coolms.retention.pruner`, registered in the Core Extension) by
 * {@see \CoolMS\CoreModule\Retention\RetentionPruneRunner} so the platform can
 * run ALL retention in one place — the `coolms:retention:prune` command and the
 * `retention.prune` scheduled handler — instead of each module's prune command
 * having to be cron-wired independently (they were shipped but nothing ran them).
 *
 * Each pruner OWNS its own grace window (baked into its own defaults — analytics
 * `retention_days`, comment 30d, identity 7d/30d); this seam exposes no `$days`
 * because the windows differ per table. A pruner whose window is disabled simply
 * returns 0. The concrete pruners keep their standalone `coolms:*:prune-*`
 * commands too — this is an additional aggregate entry point, not a replacement.
 */
interface RetentionPrunerInterface
{
    /**
     * Stable, dot-namespaced identifier (`<module>.<thing>`, e.g.
     * `analytics.events`, `comment.spam`, `identity.credentials`). Shown in the
     * prune report; treat as a slug.
     */
    public function retentionKey(): string;

    /** Short human label for the report (e.g. "Spam comments"). */
    public function retentionLabel(): string;

    /** Delete this pruner's expired rows; return the TOTAL rows removed. */
    public function pruneExpired(): int;

    /** How many rows {@see pruneExpired} would remove — the dry-run preview. */
    public function countExpired(): int;
}
