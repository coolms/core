<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

/**
 * Parsed command-line input -- immutable value object.
 *
 * Examples:
 *   "ls -l /themes"          -- command=ls, args=[/themes], opts=[-l: true]
 *   "chmod 0644 /themes/app" -- command=chmod, args=[0644, /themes/app]
 *   "rql:explain --entity=NaviNode 'filter=title cn x'"
 *       -- command=rql:explain, args=[filter=title cn x], opts=[--entity: NaviNode]
 */
final readonly class TerminalInput
{
    /**
     * @param string                     $command e.g., 'ls', 'navi:seed', 'rql:explain'
     * @param string[]                   $args    positional arguments
     * @param array<string, string|bool> $opts    flags and options
     * @param string                     $raw     original unmodified input line
     * @param string                     $stdin   piped input from a preceding command
     * @param string                     $cwd     the session's working directory, absolute
     * @param string                     $home    the caller's home directory, for `~` expansion
     */
    public function __construct(
        public string $command,
        public array $args = [],
        public array $opts = [],
        public string $raw = '',
        public string $stdin = '',
        public string $cwd = TerminalPath::ROOT,
        public string $home = TerminalPath::ROOT,
    ) {
    }

    /**
     * A positional argument resolved to an ABSOLUTE VFS path.
     *
     * This is what makes the terminal behave like a shell, and it exists here
     * rather than in each command so the rule — `~`, absolute, relative-to-cwd,
     * then lexical `.`/`..` collapsing — has exactly one implementation. A
     * command that reads {@see arg()} for a path is not wrong so much as
     * stranded at the root.
     *
     * `$default` is a raw argument, not a resolved path: passing `'.'` means
     * "the working directory", which is the shell default for `ls` and friends.
     */
    public function path(int $index, string $default = '.'): string
    {
        $raw = $this->args[$index] ?? $default;

        return TerminalPath::resolve($raw, $this->cwd, $this->home);
    }

    /**
     * Get positional argument by index.
     */
    public function arg(int $index, string $default = ''): string
    {
        return $this->args[$index] ?? $default;
    }

    /**
     * Get option value. Supports --flag, -f, and bare 'flag' lookup.
     */
    public function opt(string $name, mixed $default = null): mixed
    {
        return $this->opts[$name]
            ?? $this->opts[ltrim($name, '-')]
            ?? $this->opts['--' . ltrim($name, '-')]
            ?? $default;
    }

    /**
     * Check if a flag is present (boolean flag like -l, --recursive).
     */
    public function hasOpt(string $name): bool
    {
        return isset($this->opts[$name])
            || isset($this->opts[ltrim($name, '-')])
            || isset($this->opts['--' . ltrim($name, '-')]);
    }
}
