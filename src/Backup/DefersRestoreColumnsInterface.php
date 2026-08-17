<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * OPT-IN companion to {@see BackupContributorInterface} for a module whose tables have
 * SELF-REFERENTIAL FKs: it declares which columns must be held back to a second pass
 * on restore, so any row order is safe. Same shape as {@see ReconcilesDeletesInterface}
 * — most contributors don't need it and say nothing.
 *
 * **Why this is DECLARED rather than done privately inside `import()`.** Two engines
 * replay these rows now: the bundle restore (`import()`, via
 * {@see \CoolMS\CoreModule\Backup\BackupReaderInterface::loadTableDeferring()}) and the
 * sync apply path ({@see \CoolMS\CoreModule\ChangeFeed\SyncChangeApplier}),
 * which writes straight through {@see TableBackupPortInterface} and never calls a
 * contributor at all. A contributor that keeps its FK knowledge to itself protects the
 * first engine and leaves the second one silently corrupting the same rows —
 * the ordering hazard is a property of the SCHEMA, not of one code path, so it belongs
 * where every engine can read it.
 *
 * **What a self-ref FK does to an unordered restore, silently:** the restore engine is
 * delete-by-id + insert per row, so a child restored before its parent is inserted
 * pointing at the OLD parent row — and the parent's own delete-by-id then fires its FK
 * rule at the row that just landed. `ON DELETE SET NULL` blanks the child's pointer;
 * `ON DELETE CASCADE` deletes the child outright, and its turn has passed, so nothing
 * puts it back. Neither throws. See
 * {@see \CoolMS\CoreModule\Backup\BackupReaderInterface::loadTableDeferring()} for why
 * deferral beats a parent-first sort, and for the CHECK-coherence rule that governs
 * which columns may travel together.
 */
interface DefersRestoreColumnsInterface
{
    /**
     * Columns to null on insert and re-apply afterwards, keyed by table. Only tables
     * that need it; only NULLABLE columns; and the set per table must be COHERENT
     * under that table's CHECK constraints — a CHECK can couple an FK to a non-FK
     * column, and nulling half of such a pair produces a row the table rejects.
     *
     * @return array<string, list<string>>
     */
    public function deferredRestoreColumns(): array;
}
