<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * A bundle directory as one file, and back.
 *
 * A bundle is a directory tree; an HTTP pull is one body. The sync surface
 * therefore needs the same packing the backup download uses, and both must
 * agree byte for byte or a snapshot written by one end is unreadable at the
 * other. Declaring the two verbs here is what lets them share one
 * implementation without either feature importing the other.
 *
 * !! `extract()` must be zip-slip-safe: an entry name that escapes the
 * destination (`../`, an absolute path, a backslash, a NUL) is rejected rather
 * than written. Bundle entries are all plain relative paths, so anything else
 * is hostile.
 */
interface BundleArchiverInterface
{
    /**
     * Pack every file under `$bundleDir` into `$zipPath`, paths relative to
     * the bundle root.
     *
     * @throws BackupException when the bundle is not a directory, or the
     *                         archive cannot be written
     */
    public function archive(string $bundleDir, string $zipPath): void;

    /**
     * Unpack `$zipPath` into `$destDir`.
     *
     * @throws BackupException when the archive cannot be read, or any entry
     *                         would write outside `$destDir`
     */
    public function extract(string $zipPath, string $destDir): void;
}
