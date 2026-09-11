<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * An installer states what it needs before it runs, and what it leaves behind.
 *
 * Optional and separate from {@see VfsInstallerInterface} and
 * {@see ModuleInstallerInterface} on purpose, exactly as
 * {@see DeclaresVfsPathsInterface} is: a method on either of those would break
 * every existing implementer -- 7 and 23 of them respectively -- and an
 * installer that needs nothing has nothing to say.
 *
 * !! BOTH SIDES ARE DECLARED THE SAME WAY, AND THAT IS THE POINT. A system that
 * collects only requirements knows what is wanted and not who supplies it, so
 * it can report a missing prerequisite but cannot order anything. Provisions are
 * what make the order DERIVED rather than assigned.
 *
 * !! WHAT THIS REPLACES. Until 2026-09-11 the order was registration order --
 * alphabetical by class name for a prototype-scanned application -- and the
 * eight `priority` attributes on `coolms.module.installer` were inert, because
 * the `ModuleInstallerPriorityPass` that was documented to apply them was never
 * written. Ordering by a number is ordering by where somebody put an installer;
 * a new module has to guess one, and two equal numbers fall back to the
 * alphabet, which is the same defect one layer down.
 *
 * TOKENS ARE OPAQUE STRINGS and {@see InstallOrder} never interprets them. Use a
 * prefixed form so two modules cannot collide by accident:
 *
 *     system-user:root          the `root` system user exists
 *     system-user:media_library the `media_library` system user exists
 *     vfs-node:/                the VFS root node exists
 *
 * State what is TRUE AFTERWARDS, not what the installer did. "vfs-node:/" is a
 * fact another installer can depend on; "ran the root bootstrap" is a diary.
 */
interface DeclaresPrerequisitesInterface
{
    /**
     * Tokens that must already be true when this installer runs.
     *
     * @return list<string>
     */
    public function declaredRequirements(): array;

    /**
     * Tokens that are true once this installer has run.
     *
     * @return list<string>
     */
    public function declaredProvisions(): array;
}
