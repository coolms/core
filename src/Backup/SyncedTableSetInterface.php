<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * The set of tables backup treats as synced -- the "synced universe" -- and how
 * to address a row inside it. Two questions, and deliberately no more.
 *
 * ⚠️ This exists so a persistence adapter can ask those questions without
 * importing the thing that answers them. The registry that answers them
 * aggregates every {@see BackupContributorInterface} and therefore lives in the
 * module layer; three adapter classes type-hinted it directly, which made a
 * persistence package import a module -- upwards through the layering, and onto
 * a class its own dependencies do not include. Standalone, those imports did
 * not resolve at all: the package only ever worked because an application
 * happened to install both.
 *
 * The contract that matters is agreement with what backup actually EXPORTS. A
 * table backup syncs but this does not cover would be captured by nothing and
 * would silently drift on an edge, which is the failure the predicate exists to
 * prevent. Implementations must derive the answer from the contributors rather
 * than keep a second list beside them.
 */
interface SyncedTableSetInterface
{
    /**
     * Whether $table belongs to the synced universe.
     *
     * A table no backup contributor claims -- a runtime-tier table, or the
     * change feed's own -- is NOT synced, and callers skip it rather than
     * treating the absence as an error.
     */
    public function covers(string $table): bool;

    /**
     * The column identifying a row of $table for sync purposes, or null when
     * the table is addressed by its own primary key.
     *
     * A table synced as part of an owned collection is addressed by its OWNER,
     * not by its own id, so a caller that assumes `id` unconditionally will
     * move the wrong rows. Callers fall back to the primary key only on null.
     */
    public function ownerColumnFor(string $table): ?string;
}
