<?php

declare(strict_types=1);

namespace CoolMS\Core\Navi;

/**
 * Implemented by any module that seeds NaviGraph nodes into a NaviTree.
 *
 * Each contributor declares:
 *   - which tree it targets (e.g. 'navi.admin', 'navi.admin.topbar')
 *   - which nodes it contributes
 *
 * NaviGraphSeeder calls all tagged contributors at install time.
 * On uninstall, NaviGraphSeeder removes nodes where meta.contributor matches
 * the module name returned by getModuleName().
 *
 * Implementations are auto-tagged via DI autoconfiguration as
 * 'coolms.navigraph.contributor'.
 */
interface NaviGraphContributorInterface
{
    /** Target tree slug, e.g. 'navi.admin' */
    public function getTreeSlug(): string;

    /** Module name used as meta.contributor for ownership tracking, e.g. 'navi', 'section' */
    public function getModuleName(): string;

    /** @return NaviNodeDefinition[] */
    public function getNodes(): array;
}
