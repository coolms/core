<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * The import-side surface a {@see BackupContributorInterface} is handed.
 *
 * Exists so the Domain contract does not name the Application class that
 * implements it -- see {@see BackupWriterInterface} for why that matters.
 *
 * Every method here is called by at least one contributor, so this is the whole
 * implementation surface rather than a chosen subset.
 */
interface BackupReaderInterface
{
    /** Restore a table's canonical payload; returns rows loaded. */
    public function loadTable(string $table): int;

    /**
     * As {@see loadTable()}, but writes `$deferredColumns` in a second pass --
     * for columns whose target row does not exist yet on the first.
     *
     * @param list<string> $deferredColumns
     */
    public function loadTableDeferring(string $table, array $deferredColumns): int;

    /**
     * A table's rows from the archive WITHOUT writing them, for a contributor
     * that must inspect or transform before restoring.
     *
     * @return list<array<int|string, mixed>>
     */
    public function readRows(string $table): array;

    /**
     * Write a PRE-BUILT row list into `$table`; the partner of
     * {@see readRows()}.
     *
     * @param list<array<int|string, mixed>> $rows
     */
    public function restoreRows(string $table, array $rows): int;

    /** @param array<string, mixed> $columns */
    public function updateRow(string $table, int|string $id, array $columns): void;

    /**
     * Delete live rows absent from the archive, so a restore converges rather
     * than merging. `$scopeEquals` narrows the sweep to rows the contributor owns.
     *
     * @param array<string, scalar> $scopeEquals
     */
    public function reconcileTable(string $table, bool $dryRun, array $scopeEquals = [], string $idColumn = 'id'): int;

    /**
     * {@see reconcileTable()} for a table keyed by a composite rather than a
     * single id column.
     *
     * @param list<string>          $keyColumns
     * @param array<string, scalar> $scopeEquals
     */
    public function reconcileCompositeTable(string $table, array $keyColumns, bool $dryRun, array $scopeEquals = []): int;

    /**
     * {@see reconcileTable()} restricted to rows whose `$membershipColumn` falls
     * in `$allowedGroupKeys` -- rows outside those groups are never deleted.
     *
     * @param list<string> $allowedGroupKeys
     */
    public function reconcileTableWithinGroups(string $table, string $membershipColumn, array $allowedGroupKeys, bool $dryRun, string $idColumn = 'id'): int;

    /**
     * Distinct live values of `$column`, for a contributor deciding what its
     * reconcile scope should be.
     *
     * @param array<string, scalar> $scopeEquals
     *
     * @return list<string>
     */
    public function liveValues(string $table, string $column, array $scopeEquals = []): array;

    /** @return list<string> */
    public function blobHashes(string $namespace = 'blobs'): array;

    public function readBlob(string $hash, string $namespace = 'blobs'): string;
}
