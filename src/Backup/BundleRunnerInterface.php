<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * Write a bundle, or restore one.
 *
 * ## Why the platform declares this without owning an engine
 *
 * A bundle is the platform's data FORMAT -- a manifest, a table payload per
 * contributor, sharded blobs -- and two very different features produce one: a
 * backup, and the snapshot an edge pulls from its controller. They agree on the
 * format and share the engine, and the engine writes files and restores rows,
 * which makes it a module's to own rather than a platform package's.
 *
 * So the platform declares the two verbs and the manifest they speak in
 * ({@see BackupManifest}), and whichever module owns backup answers. A feature
 * that needs a bundle asks for this port and stays ignorant of who builds one --
 * which is what lets the sync module produce snapshots without depending on the
 * backup module.
 *
 * !! A restore WRITES every contributor's rows. An implementation is expected
 * to be idempotent (delete-then-insert by id, no wrapping transaction) so a
 * partial restore self-heals by being run again.
 */
interface BundleRunnerInterface
{
    /**
     * Export the selected tiers into a fresh bundle at `$bundleDir`.
     *
     * @param list<BackupTier> $tiers    which data classes to include
     * @param list<string>     $onlyKeys restrict to these backup keys (empty = all)
     *
     * @return list<array{key: string, label: string, tier: string, records: int}>
     */
    public function create(string $bundleDir, array $tiers, array $onlyKeys = []): array;

    /**
     * Restore a bundle. Additive by default; `$reconcileDeletes` also removes
     * live rows the bundle does not have, which is DESTRUCTIVE and opt-in.
     *
     * @param list<string> $onlyKeys restrict to these backup keys (empty = all)
     *
     * @return list<array{key: string, label: string, restored: int, status: string, deleted: int}>
     */
    public function restore(
        string $bundleDir,
        bool $dryRun = false,
        array $onlyKeys = [],
        bool $reconcileDeletes = false,
    ): array;

    /**
     * The bundle's manifest, which also says whether this code may restore it.
     *
     * @throws BackupException when there is no manifest, or it is malformed
     */
    public function readManifest(string $bundleDir): BackupManifest;
}
