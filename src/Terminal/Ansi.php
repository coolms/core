<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

/**
 * ANSI escape code helper for colorized terminal output.
 *
 * Output from command handlers uses these helpers to produce
 * richly colored text that xterm.js renders correctly.
 *
 * Usage in command handlers:
 *   yield Ansi::success('Directory created: /uploads/media');
 *   yield Ansi::error('Permission denied: /system/kernel.php');
 *   yield Ansi::blue('drwxr-xr-x') . '  root  root  themes/';
 */
final class Ansi
{
    // Base colors
    public static function green(string $t): string
    {
        return "\e[32m{$t}\e[0m";
    }

    public static function red(string $t): string
    {
        return "\e[31m{$t}\e[0m";
    }

    public static function yellow(string $t): string
    {
        return "\e[33m{$t}\e[0m";
    }

    public static function blue(string $t): string
    {
        return "\e[34m{$t}\e[0m";
    }

    public static function cyan(string $t): string
    {
        return "\e[36m{$t}\e[0m";
    }

    public static function white(string $t): string
    {
        return "\e[37m{$t}\e[0m";
    }

    public static function magenta(string $t): string
    {
        return "\e[35m{$t}\e[0m";
    }

    // Text styles
    public static function bold(string $t): string
    {
        return "\e[1m{$t}\e[0m";
    }

    public static function dim(string $t): string
    {
        return "\e[2m{$t}\e[0m";
    }

    public static function italic(string $t): string
    {
        return "\e[3m{$t}\e[0m";
    }

    public static function underline(string $t): string
    {
        return "\e[4m{$t}\e[0m";
    }

    // Semantic shortcuts
    public static function success(string $t): string
    {
        return self::green("✓ {$t}");
    }

    public static function error(string $t): string
    {
        return self::red("✗ {$t}");
    }

    public static function warning(string $t): string
    {
        return self::yellow("⚠ {$t}");
    }

    public static function info(string $t): string
    {
        return self::cyan("ℹ {$t}");
    }

    // VFS-specific
    public static function dir(string $t): string
    {
        return self::blue("{$t}/");
    }

    public static function system(string $t): string
    {
        return self::red("[SYSTEM] {$t}");
    }

    public static function hidden(string $t): string
    {
        return self::dim("{$t} [hidden]");
    }

    public static function prompt(): string
    {
        return self::green('coolms> ');
    }

    // SQL keyword highlighting
    public static function sqlKeyword(string $t): string
    {
        return self::blue(strtoupper($t));
    }

    public static function sqlTable(string $t): string
    {
        return self::green($t);
    }

    public static function sqlParam(string $t): string
    {
        return self::yellow($t);
    }

    /**
     * Colorize SQL string by highlighting keywords.
     */
    public static function sql(string $sql): string
    {
        return (string) preg_replace_callback(
            '/\b(SELECT|FROM|WHERE|ORDER BY|GROUP BY|LIMIT|OFFSET|AND|OR|NOT|LIKE|IN|IS NULL|IS NOT NULL|JOIN|LEFT|INNER|ON|AS|COUNT|DISTINCT)\b/i',
            fn (array $m) => self::sqlKeyword($m[0]),
            $sql,
        );
    }
}
