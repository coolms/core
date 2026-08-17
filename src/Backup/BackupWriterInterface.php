<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * The export-side surface a {@see BackupContributorInterface} is handed.
 *
 * Exists so the Domain contract does not name the Application class that
 * implements it -- without this, `coolms/core` (Domain) would depend on
 * `coolms/core-module` (Application) and the two packages would cycle.
 *
 * Scope is measured, not mirrored: these are the methods contributors actually
 * call. The implementation's `recordsWritten()`/`blobsWritten()` are deliberately
 * absent -- nothing calls them at all.
 */
interface BackupWriterInterface
{
    /**
     * Dump a whole table into `data/<key>/<table>.json`; returns rows written.
     * Streams row by row, so peak memory is one row rather than the whole table.
     */
    public function dumpTable(string $table): int;

    /**
     * A live table's rows WITHOUT writing them, for a contributor that filters or
     * transforms before handing survivors to {@see dumpRows()}. Materialises the
     * whole table -- prefer {@see streamTable()} unless random access is needed.
     *
     * @return list<array<string, mixed>>
     */
    public function readTable(string $table): array;

    /**
     * A live table's rows one at a time, without writing them: the
     * bounded-memory {@see readTable()}. A cursor, not a buffer -- each call
     * re-reads the table.
     *
     * @return iterable<array<string, mixed>>
     */
    public function streamTable(string $table): iterable;

    /**
     * Write a PRE-BUILT/filtered row list as `$table`'s canonical payload. Takes
     * an iterable so a filtering contributor can pass a generator and never hold
     * the kept rows in memory; rows are consumed exactly once.
     *
     * @param iterable<array<int|string, mixed>> $rows
     */
    public function dumpRows(string $table, iterable $rows): int;

    /**
     * An arbitrary SMALL JSON payload under this contributor's dir. Table
     * payloads go through {@see dumpRows()}, which streams.
     *
     * @param array<mixed> $data
     */
    public function putJson(string $name, array $data): void;

    /**
     * Store raw bytes (NOT JSON) sha256-sharded under `<namespace>/`, for
     * contributors whose rows reference file bytes living outside the DB.
     */
    public function putBlob(string $hash, string $bytes, string $namespace = 'blobs'): void;

    /** @return list<string> */
    public function tablesWritten(): array;
}
