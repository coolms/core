<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * A module-published seam for backup/restore: something that knows how to
 * serialise its OWN data slice into a portable bundle and replay it back.
 * Implementers are collected (tag `coolms.backup.contributor`, registered in the
 * Core Extension) by {@see \CoolMS\CoreModule\Backup\BackupRunner} so the
 * platform can back up/restore everything in one place —
 * `coolms:backup:create` / `coolms:backup:restore` — with every module owning
 * exactly how to export + import its own tables/blobs.
 *
 * This mirrors the retention-pruner seam ({@see \CoolMS\Core\Retention\RetentionPrunerInterface}):
 * an L0 interface, a Core autoconfigure tag, and a runner that pins the tagged
 * iterator via `#[AutowireIterator]`. It is deliberately the same shape so the
 * bundle can later double as the controller→edge SYNC snapshot format.
 *
 * **Idempotent by construction.** Every aggregate is keyed by a globally-unique
 * UUID-v7 with no autoincrement FKs (the soft-ref discipline), so `import` is a
 * safe upsert-by-id: re-importing a bundle, or importing into a partially-seeded
 * instance, converges rather than colliding.
 *
 * **Security boundary (enforced by the runner/writer, restated here):** a
 * contributor serialises ONLY its own persisted rows/blobs. It MUST NOT read or
 * emit secret material — the master key (`COOLMS_SECRET_MASTER_KEY`),
 * `COOLMS_SECRET_*` / `VAULT_TOKEN`, `.env*` files, or `var/secrets/`. DB columns
 * that are already ciphertext (e.g. sealed mailbox credentials) may travel as
 * opaque ciphertext; they are useless — and safe — without the out-of-band
 * master key.
 */
interface BackupContributorInterface
{
    /**
     * Stable slug (`<module>` or `<module>.<slice>`, e.g. `calendar`). Names the
     * contributor's payload directory inside the bundle and is used by
     * `--only` filtering and {@see restoreAfter}. Treat as a slug.
     */
    public function backupKey(): string;

    /** Short human label for the report (e.g. "Calendars"). */
    public function backupLabel(): string;

    /**
     * Which data class this contributor owns — drives `--tier` / profile
     * filtering. See {@see BackupTier}.
     */
    public function tier(): BackupTier;

    /**
     * Backup keys that MUST be imported before this one (dependency ordering
     * for a restore). Empty when the slice is self-contained (soft-ref UUID
     * data usually is). The runner topo-sorts on this.
     *
     * @return list<string>
     */
    public function restoreAfter(): array;

    /**
     * The DB tables this contributor serialises — the SAME set its {@see export}
     * writes (typically the contributor's own table constants). Declaring it makes
     * the "synced universe" programmatically enumerable ({@see \CoolMS\CoreModule\Backup\BackupTableRegistry}
     * aggregates every contributor's), which the controller→edge sync
     * change-feed needs: the feed's row-change CAPTURE scope must be provably EQUAL
     * to what backup EXPORTS, so it never silently misses a synced write (the
     * failure mode of sourcing the delta from a partial log). A contract test pins
     * `tables()` against the tables `export()` actually writes.
     *
     * Only DB tables — out-of-band payloads (VFS blob bytes) are not tables and are
     * NOT listed here; those sync via their own channel.
     *
     * **ORDER IS THE MODULE'S FK CONTRACT: parents first.** (It was long documented
     * as "irrelevant", on the reasoning that the registry de-duplicates into
     * a set for `covers()`. It does — but that set is not the only consumer. The
     * sync applier replays these same tables WITHOUT going through
     * `import()`, and had no FK order available anywhere, because the one place each
     * module's order was written down was its own private `import()` loop.) So: list
     * referenced tables before the tables that reference them; `import()` MUST walk
     * this order forward and `reconcileDeletes()` MUST walk it reversed. Cross-MODULE
     * dependencies go in {@see restoreAfter()} instead; intra-table (self-referential)
     * hazards go in {@see DefersRestoreColumnsInterface}, which no table order can fix.
     *
     * @return list<string>
     */
    public function tables(): array;

    /**
     * Serialise this module's slice into the bundle via the writer (typically a
     * few `$writer->dumpTable('coolms_...')` calls; contributors with blobs use
     * `$writer->putBlob(...)`). Return the number of records written.
     */
    public function export(BackupWriterInterface $writer): int;

    /**
     * Replay this module's slice from the bundle via the reader (idempotent
     * upsert-by-id). Return the number of records restored.
     */
    public function import(BackupReaderInterface $reader): int;
}
