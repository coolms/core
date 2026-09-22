<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * Everything the contributors' tables say about themselves, in one port.
 *
 * {@see SyncedTableSetInterface} answers the two questions a persistence
 * adapter asks -- is this table synced, and what addresses a row in it. This
 * extends that with the rest of what the contributors' declarations imply:
 * which tables there are, what order they restore in, which columns must be
 * deferred past a first pass, and which tier a table belongs to.
 *
 * ## Why it is a port at all
 *
 * The answers are DERIVED from every {@see BackupContributorInterface} in the
 * installation, so the thing that answers aggregates modules and belongs to
 * whichever module owns backup. What ASKS is not only that module: applying a
 * change feed needs the same restore order and the same deferred columns, or
 * the two would drift and a snapshot would restore in an order its own feed
 * does not replay. One port, one registry, two readers.
 *
 * !! An implementation must derive every answer from the contributors rather
 * than keep a second list beside them. A table backup exports but this does not
 * cover is captured by nothing and drifts silently on an edge.
 */
interface BackupTableSetInterface extends SyncedTableSetInterface
{
    /**
     * Every table any contributor claims, sorted.
     *
     * @return list<string>
     */
    public function allTables(): array;

    /**
     * Every table in RESTORE order -- parents before the rows that reference
     * them, so a foreign key never blocks an insert.
     *
     * @return list<string>
     */
    public function orderedTables(): array;

    /**
     * The given tables in restore order; tables no contributor claims keep
     * their relative order at the end.
     *
     * @param list<string> $tables
     *
     * @return list<string>
     */
    public function sortByRestoreOrder(array $tables): array;

    /**
     * Columns of `$table` that cannot be written in the first pass -- a
     * self-reference, or a reference to a table restored later.
     *
     * @return list<string>
     */
    public function deferredColumnsFor(string $table): array;

    /** Which data class `$table` belongs to, or null when nobody claims it. */
    public function tierFor(string $table): ?BackupTier;
}
