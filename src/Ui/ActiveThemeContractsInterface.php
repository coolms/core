<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

/**
 * The contracts the ACTIVE theme declares, for the readers that are not the
 * theme's own commands: `coolms:install` (a module installed after the theme
 * meets it here) and the app-config manifest (what the host is told to
 * mount). The Theme module answers; where no theme module is installed the
 * port is absent and both readers treat every entry as unused.
 */
interface ActiveThemeContractsInterface
{
    /** Null when no theme is active, or the active theme declares no contracts. */
    public function active(): ?ThemeContracts;
}
