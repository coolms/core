<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * OPT-IN companion to {@see BackupContributorInterface}: a contributor that also
 * knows how to reconcile DELETES — remove its live rows that are ABSENT from a
 * snapshot (the rows deleted on the source since the target last synced).
 *
 * This closes the "additive-only" gap of a pure upsert-restore:
 * `import()` re-inserts every snapshot row but can never notice a row the source
 * DELETED, so on a controller→edge sync a deleted controller row lingers on the
 * edge forever. Reconcile is the second half.
 *
 * It is OPT-IN and DESTRUCTIVE, so:
 * - A contributor implements this ONLY when its snapshot is authoritative + COMPLETE
 *   for the tables it reconciles (true for whole-table config exports; NOT for a
 *   contributor whose export is a filtered subset — unless it scopes the reconcile
 *   to the SAME filter, so rows it never exports, e.g. module-shipped definitions,
 *   are never deleted). {@see BackupReaderInterface::reconcileTable()} takes a scope for this.
 * - The runner only reconciles when the operator explicitly asks
 *   (`--reconcile-deletes`); the default restore stays purely additive + safe.
 *
 * Implementers MUST respect FK order (delete child rows before the parent rows they
 * reference — the reverse of the {@see BackupContributorInterface::import()} insert
 * order) and MUST only ever touch their OWN tables. Called AFTER `import()` in the
 * same restore, in reverse-dependency order across contributors.
 */
interface ReconcilesDeletesInterface
{
    /**
     * Delete this contributor's live rows absent from the snapshot in `$reader`.
     * Returns rows deleted — or, when `$dryRun`, the number that WOULD be deleted
     * (writing nothing).
     */
    public function reconcileDeletes(BackupReaderInterface $reader, bool $dryRun): int;
}
