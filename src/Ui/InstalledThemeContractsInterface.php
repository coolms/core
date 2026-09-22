<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

/**
 * The host contracts the INSTALLED themes declare, for the readers that are
 * not the theme's own commands: `coolms:install` (a module installed after
 * the theme meets it here) and the app-config manifest (what the host is
 * told to mount). The Theme module answers; where no theme module is
 * installed the port is absent and both readers treat every entry as unused.
 *
 * Installed, not active: the console is served by the theme that ships it,
 * which is never "activated" -- activation is the site's notion. One
 * installed theme per contract; a second is refused by name.
 */
interface InstalledThemeContractsInterface
{
    /**
     * {@see HostContracts::none()} when no theme declares anything, or when
     * two declare one contract (which is logged).
     */
    public function installed(): HostContracts;
}
