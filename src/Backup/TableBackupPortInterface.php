<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * Port for row-level export/import of a single DB table — the low-level engine
 * a {@see BackupContributorInterface} rides on. Kept as a Domain port so the
 * Application-layer writer/reader depend on the abstraction; the raw DBAL
 * raw implementation is supplied by whichever persistence adapter is
 * installed (`coolms/core-doctrine` provides the DBAL one).
 *
 * Export = `SELECT *` (raw scalar/null values — a JSONB column comes back as its
 * JSON text, a timestamp as its string; both round-trip on re-insert against the
 * same engine family). Restore = per row `DELETE FROM t WHERE id` + `INSERT`, a
 * portable idempotent upsert-by-id (UUID-keyed, no autoincrement FKs).
 */
interface TableBackupPortInterface
{
    /**
     * Dump every row of `$table`.
     *
     * Materialises the WHOLE table in memory. Prefer {@see streamTable()} for
     * anything that only walks the rows once (an export writing them straight
     * out) — a large install's `coolms_vfs_nodes` does not fit twice.
     *
     * @return list<array<string, mixed>>
     */
    public function dumpTable(string $table): array;

    /**
     * Every row of `$table`, yielded ONE AT A TIME instead of collected into an
     * array — the bounded-memory sibling of {@see dumpTable()} and the path
     * {@see \CoolMS\CoreModule\Backup\BackupWriterInterface::dumpTable()} exports on.
     *
     * The rows are identical in shape to `dumpTable()`'s (raw scalar/null values,
     * DB-generated columns stripped), so a consumer can swap one for the other.
     *
     * **What this does and does not bound.** It removes the PHP-array copy of the
     * result set, which is the dominant cost (a hydrated assoc row is several times
     * the size of the driver's packed buffer). It does NOT make the underlying
     * driver fetch lazily: PDO still buffers the result set client-side unless a
     * server-side cursor is used. So this bounds the PHP heap, not the total RSS —
     * enough to keep an export off the `memory_limit` ceiling, not a substitute for
     * chunking should a table ever outgrow the driver buffer too.
     *
     * @return iterable<array<string, mixed>>
     */
    public function streamTable(string $table): iterable;

    /**
     * Dump the CURRENT rows of `$table` whose `$idColumn` is one of `$ids` (chunked
     * IN-list), in the SAME shape as {@see dumpTable()} — raw scalar/null values with
     * DB-generated columns stripped — so they feed straight into {@see restoreRows()}.
     * Empty `$ids` (or ids matching no live row) → `[]`.
     *
     * The row-HYDRATION half of the controller→edge change feed: the
     * lean CDC log carries only `(table, row-UUID, op)`, no payload, so an edge that
     * reads an `upsert` delta fetches the row's current data here — once per distinct
     * changed row, no matter how far behind the edge is — before replaying it. Delete
     * deltas need no hydration (the UUID suffices).
     *
     * `$idColumn` is `id` for an ordinary table. For an OWNED-COLLECTION table (a
     * many-to-many join table, declared via
     * {@see SyncsAsOwnedCollectionInterface}) it is the OWNER column and `$ids` are owner
     * ids, so the call returns those owners' whole current sets — the input to the
     * applier's set-replace. Callers must resolve the column via
     * {@see \CoolMS\CoreModule\Backup\BackupTableRegistry::ownerColumnFor()} rather
     * than hard-coding `id`.
     *
     * @param list<string> $ids
     *
     * @return list<array<string, mixed>>
     */
    public function dumpRowsByIds(string $table, string $idColumn, array $ids): array;

    /**
     * Restore rows into `$table` (delete-by-id + insert); returns rows written.
     *
     * @param list<array<int|string, mixed>> $rows
     */
    public function restoreRows(string $table, array $rows): int;

    /**
     * Set specific columns on ONE existing row (targeted `UPDATE ... WHERE id`,
     * with NO delete-by-id). This is how a contributor closes a circular FK: it
     * restores the parent row with the cycle-closing column NULLed via
     * {@see restoreRows()}, restores the rows it points at, then calls this to
     * re-point it. A second `restoreRows()` could not — its delete-by-id would
     * CASCADE-wipe the rows just inserted. No-op when `$columns` is empty.
     *
     * @param array<string, mixed> $columns
     */
    public function updateRow(string $table, int|string $id, array $columns): void;

    /**
     * The `$idColumn` values of every live row of `$table` matching `$scopeEquals`
     * (each `column => value` adds `AND column = value`; empty = the whole table),
     * as strings — the "what is currently here" half of a delete-reconcile
     * ({@see ReconcilesDeletesInterface}). `$scopeEquals`
     * lets a contributor scope the live set to the SAME filter it exported with, so
     * reconcile never considers rows it does not own.
     *
     * @param array<string, scalar> $scopeEquals
     *
     * @return list<string>
     */
    public function liveIds(string $table, string $idColumn, array $scopeEquals = []): array;

    /**
     * Delete the rows of `$table` whose `$idColumn` is one of `$ids` (chunked to
     * stay under driver placeholder limits); returns rows deleted. Empty `$ids` is
     * a no-op. The delete half of a reconcile — the caller has already diffed the
     * live ids against the snapshot to compute exactly which rows are stale.
     *
     * @param list<string> $ids
     */
    public function deleteByIds(string $table, string $idColumn, array $ids): int;

    /**
     * The `$idColumn` values of every live row of `$table` whose `$membershipColumn`
     * is IN `$allowedValues` — the whitelist half of a GROUPED delete-reconcile
     * ({@see \CoolMS\CoreModule\Backup\BackupReaderInterface::reconcileTableWithinGroups()}).
     * Lets a contributor restrict the live set to rows belonging to an allowed
     * partition (e.g. a Definition ladder's authored, non-module-owned definition
     * ids) in ONE query — instead of one query per group, which is O(groups). Empty
     * `$allowedValues` → empty result (nothing whitelisted). When
     * `$membershipColumn === $idColumn` it degenerates to "the subset of these ids
     * that are live". Chunked to stay under driver placeholder limits.
     *
     * @param list<string> $allowedValues
     *
     * @return list<string>
     */
    public function liveIdsWhereIn(string $table, string $idColumn, string $membershipColumn, array $allowedValues): array;

    /**
     * Composite-key sibling of {@see liveIds()} for a table whose PK is MULTIPLE
     * columns and has no single `id` (a join table like `user_groups(user_id,
     * group_id)`). Returns the `$keyColumns` of every live row matching
     * `$scopeEquals`, each as an ordered `column => value` map (values stringified)
     * — the "what is currently here" half of a composite delete-reconcile.
     *
     * @param list<string>          $keyColumns
     * @param array<string, scalar> $scopeEquals
     *
     * @return list<array<string, string>>
     */
    public function liveCompositeKeys(string $table, array $keyColumns, array $scopeEquals = []): array;

    /**
     * Delete the rows of `$table` whose composite `$keyColumns` match one of
     * `$keys` (`(col1, col2) IN ((?,?), …)`, chunked); returns rows deleted. Empty
     * `$keys` is a no-op. The delete half of a composite reconcile — the caller has
     * already diffed the live composite keys against the snapshot.
     *
     * @param list<string>                $keyColumns
     * @param list<array<string, string>> $keys
     */
    public function deleteByCompositeKeys(string $table, array $keyColumns, array $keys): int;
}
