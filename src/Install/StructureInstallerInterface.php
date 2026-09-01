<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * A module that creates its own directory structure at install time.
 *
 * Run by `bin/console coolms:install` before module data, so installers that
 * seed content can assume their directories exist. Implementations are collected
 * by tag, not by this interface, so the kernel never names the module providing
 * the storage -- see {@see VfsInstallerInterface}, which extends this and
 * carries the `coolms.vfs.installer` tag.
 *
 * MUST be idempotent -- safe to run multiple times.
 */
interface StructureInstallerInterface
{
    public function installStructure(): void;
}
