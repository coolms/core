<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * An installer states which VFS paths it claims.
 *
 * Optional and separate from {@see VfsInstallerInterface} on purpose: adding a
 * method to that one would break every existing implementer, and an installer
 * that claims nothing has nothing to say.
 *
 * ⚠️ This is ADVISORY. Nothing refuses an install over it today. That is a
 * deliberate first step and not an oversight -- but a declaration nothing reads
 * is worthless, so `coolms:install` reads these and reports two modules
 * claiming the same path before it runs either of them. Enforcement can follow
 * when a module registry makes it necessary; the declarations will already be
 * there and already true.
 *
 * State the roots the module owns, not every node it will ever write:
 * `/media`, not `/media/collections/2026`.
 */
interface DeclaresVfsPathsInterface
{
    /**
     * Absolute VFS paths this installer creates or takes ownership of.
     *
     * @return list<string>
     */
    public function declaredVfsPaths(): array;
}
