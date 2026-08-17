<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

use Generator;

/**
 * Contract for all terminal command handlers.
 *
 * Implementations are tagged 'coolms.terminal.command' and registered
 * in CommandRegistry by TerminalCommandPass.
 */
interface TerminalCommandHandlerInterface
{
    /**
     * Command name used in the terminal, e.g., 'ls', 'navi:seed', 'rql:explain'.
     */
    public function getCommand(): string;

    /**
     * Short one-line description shown in `help` listing.
     */
    public function getDescription(): string;

    /**
     * Usage string shown in `help <command>`.
     * Example: 'ls [options] [path]'.
     */
    public function getUsage(): string;

    /**
     * Minimum Symfony role required to execute this command.
     * One of 'ROLE_USER', 'ROLE_ADMIN', or 'ROLE_ROOT'.
     */
    public function getRequiredRole(): string;

    /**
     * Execute the command and yield output lines one by one.
     *
     * A command may also yield a typed SIGNAL that asks the session to change
     * (today only {@see ChangeDirectory}). Signals are values rather than
     * formatted lines precisely so no ordinary output can impersonate one — the
     * processor dispatches on TYPE, not on a prefix.
     *
     * @return Generator<string|ChangeDirectory>
     */
    public function execute(TerminalInput $input): Generator;

    /**
     * Return tab completion suggestions for the current partial input.
     *
     * $partial is the word currently being typed (last token in the input line).
     *
     * @return string[]
     */
    public function complete(TerminalInput $input, string $partial): array;
}
