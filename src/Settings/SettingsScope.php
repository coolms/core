<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * Where a setting's value belongs.
 *
 * - **Project:** the project's configuration, the same for every installation of it. A settings store may keep it in
 *   a file the project commits.
 * - **Installation:** a fact about one installation -- its base URL, its hosts, the mailbox it sends from, whether it
 *   is production, anything naming a specific server, and anything set per site. Never written to a file a project
 *   commits: a store keeps it in its database, the environment pins it, or the module that owns it keeps it.
 *
 * A pinned key ({@see ModuleSettingsDefinition::$envPins}) is always Installation: the environment that pins it is
 * the installation's.
 */
enum SettingsScope: string
{
    case Project = 'project';
    case Installation = 'installation';
}
