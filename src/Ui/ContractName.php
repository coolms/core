<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

use function array_filter;
use function array_keys;
use function sort;

/**
 * The host contracts' names, and the names two of them had.
 *
 * `console` is now `admin` and `desk` is now `workspace`; `site` is unchanged (renamed 2026-10-06). An
 * old name is read as a DEPRECATED ALIAS of its new one wherever a name is read -- a module's `ui.yaml` entry and a
 * theme's `contracts:` -- so a package still on one name meets a package already on the other. The aliases are
 * removed in the release after the one in which no package is counted on an old name.
 *
 * Every reader compares canonical names ({@see canonical()}); only a report on who is still on an old name reads
 * the name as written.
 */
final class ContractName
{
    public const string ADMIN = 'admin';

    public const string WORKSPACE = 'workspace';

    public const string SITE = 'site';

    /**
     * Each deprecated name, and the name it is now.
     *
     * @var array<string, string>
     */
    public const array DEPRECATED = [
        'console' => self::ADMIN,
        'desk' => self::WORKSPACE,
    ];

    /** The name a contract is known by: an old name's new one, any other name as given. */
    public static function canonical(string $name): string
    {
        return self::DEPRECATED[$name] ?? $name;
    }

    public static function isDeprecated(string $name): bool
    {
        return isset(self::DEPRECATED[$name]);
    }

    /**
     * The deprecated names a contract is also known by, sorted: what a reader written before the rename still asks.
     *
     * @return list<string>
     */
    public static function aliasesOf(string $canonical): array
    {
        $aliases = array_keys(array_filter(self::DEPRECATED, static fn (string $now): bool => $now === $canonical));
        sort($aliases);

        return $aliases;
    }
}
