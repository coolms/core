<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

/**
 * Each module that needs VFS directories implements this interface.
 *
 * Tagged with 'coolms.vfs.installer' -- auto-registered via DI autoconfiguration.
 *
 * !! THE ORDER IS NOT A DEPENDENCY ORDER, AND NEVER HAS BEEN. This said
 * "called by `bin/console coolms:install` in dependency order" until 2026-09-11.
 * There is no mechanism behind that sentence and there never was one: the
 * command iterates the tagged services with a plain `foreach` -- no sort, no
 * priority handling -- so the effective order is REGISTRATION order, which for a
 * prototype-scanned application is alphabetical by class name.
 *
 * Measured by iterating the iterator the command actually receives:
 *
 *     Decision, Document, I18n, Identity, Media, VfsCore, Workflow
 *
 * Note where that puts things: the installer that creates the VFS core structure
 * runs SIXTH of seven, and the first to run needs a root user that a later phase
 * creates. `debug:container --tag` cannot show this -- it sorts its own output
 * alphabetically, which is how the old sentence could look true to anyone who
 * checked it that way.
 *
 * So an implementation must NOT assume a peer has run unless it has DECLARED
 * that it needs it. All seven require the `admin` system user by name and
 * discovered its absence at the point of use, mid-install, rather than before
 * anything runs -- until they declared it.
 *
 * The derived order exists since 2026-09-11: {@see InstallOrder} computes it
 * from {@see DeclaresPrerequisitesInterface} -- what each installer requires
 * and what it provides -- and refuses before the first installer executes when
 * a prerequisite is unsatisfiable or the declarations form a cycle. It is
 * opt-in per installer: one that declares nothing is placed by the tie-break,
 * which is still the class name. Whether `coolms:install` uses it depends on
 * the core-bundle version; the paragraph above describes the order an
 * undeclared installer gets either way.
 *
 * Adds nothing to {@see StructureInstallerInterface}: it exists so the tag has a
 * VFS-named contract to autoconfigure on, while the kernel types against Core's
 * -- and a structure installer that creates no directory implements Core's
 * contract alone.
 *
 * MUST be idempotent -- safe to run multiple times.
 */
interface VfsInstallerInterface extends StructureInstallerInterface
{
}
