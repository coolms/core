<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

use function array_pop;
use function explode;
use function implode;
use function ltrim;
use function rtrim;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * Resolves a terminal path argument against a working directory.
 *
 * Until now every VFS terminal command took an ABSOLUTE path — `ls` defaulted
 * to `/`, and `cat themes/x` meant nothing. That is the one thing that makes
 * the terminal not feel like a shell, and the fix is not per-command: it is a
 * single resolution rule that every command reaches through
 * {@see Command\TerminalInput::path()}.
 *
 * ## The rules, in the order they apply
 *
 *  - `~` alone, or a `~/…` prefix, expands to the caller's home directory.
 *  - a leading `/` is absolute and the cwd is ignored.
 *  - anything else is relative to the cwd.
 *  - `.` and `..` segments are then collapsed LEXICALLY, and `..` at the root
 *    stays at the root (`/..` is `/`, as in a real shell).
 *
 * ## Lexical, deliberately
 *
 * Collapsing happens on the string, without consulting the VFS. That differs
 * from a real kernel, which resolves symlinks first — so under a symlinked
 * directory, `cd a/b/..` returns to `a` rather than to the link target's
 * parent. The alternative is a VFS round-trip per segment on every argument of
 * every command, and this platform's symlinks are terminal-node links rather
 * than deep directory redirects (the VFS manager contract
 * distinguishes `findByPath` from `findByPathFollowingLink` precisely because
 * the difference is usually not wanted). The trade is documented rather than
 * hidden.
 *
 * Returns paths WITHOUT a trailing slash (except the root itself), which is the
 * form `materializedPath` uses — so a resolved path can be compared to a stored
 * one without normalising again.
 */
final readonly class TerminalPath
{
    public const string ROOT = '/';

    /**
     * @param string $argument the raw path as typed (may be empty)
     * @param string $cwd      the session's working directory (absolute)
     * @param string $home     the caller's home directory, for `~` expansion
     */
    public static function resolve(string $argument, string $cwd, string $home = self::ROOT): string
    {
        $raw = trim($argument);

        // An omitted path means "here" — the shell default that makes bare `ls`
        // list the working directory rather than the root.
        if ('' === $raw) {
            return self::normalize($cwd);
        }

        if ('~' === $raw) {
            return self::normalize($home);
        }
        if (str_starts_with($raw, '~/')) {
            return self::normalize(rtrim($home, '/') . '/' . ltrim($raw, '~/'));
        }

        if (str_starts_with($raw, '/')) {
            return self::normalize($raw);
        }

        return self::normalize(rtrim($cwd, '/') . '/' . $raw);
    }

    /**
     * Collapse `.` / `..` / duplicate separators in an ABSOLUTE path.
     *
     * `..` past the root is clamped rather than rejected: `cd /..` in a shell
     * leaves you at `/`, and a terminal that errored there would be surprising
     * without protecting anything — the VFS permission check is what actually
     * guards access.
     */
    public static function normalize(string $path): string
    {
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ('' === $segment || '.' === $segment) {
                continue;
            }
            if ('..' === $segment) {
                array_pop($segments); // no-op at the root — clamped, not an error
                continue;
            }
            $segments[] = $segment;
        }

        return [] === $segments ? self::ROOT : self::ROOT . implode('/', $segments);
    }

    /**
     * Render a path for the PROMPT: the home directory contracts back to `~`.
     *
     * The inverse of `~` expansion, and the reason it exists is width — a home
     * path is `/home/{uuid}`, which would eat most of a prompt line and tell the
     * reader nothing they did not know.
     */
    public static function forPrompt(string $path, string $home): string
    {
        $normalized = self::normalize($path);
        $normalizedHome = self::normalize($home);

        if (self::ROOT === $normalizedHome) {
            return $normalized; // no home to contract against
        }
        if ($normalized === $normalizedHome) {
            return '~';
        }
        if (str_starts_with($normalized, $normalizedHome . '/')) {
            return '~' . substr($normalized, strlen($normalizedHome));
        }

        return $normalized;
    }
}
