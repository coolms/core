<?php

declare(strict_types=1);

namespace CoolMS\Core\Console;

/**
 * The vocabulary a module uses to offer one of its console commands to a web
 * terminal -- a tag name and the keys of its attributes, and nothing else.
 *
 * ## Why this is constants and not an interface
 *
 * The direction (2026-09-22) is that every module has its own console commands
 * and a terminal USES them; no module implements a terminal's interface. A
 * module therefore writes an ordinary `#[AsCommand]` class and, in its own
 * compiler pass, tags that service:
 *
 * ```php
 * $container->findDefinition(LsCommand::class)
 *     ->addTag(ConsoleExposure::TAG, [
 *         ConsoleExposure::NAME => 'ls',
 *         ConsoleExposure::PERMISSION => VfsPermissions::READ,
 *         ConsoleExposure::ELEVATION => false,
 *     ]);
 * ```
 *
 * The exposure is POLICY -- which commands a terminal offers, under which
 * permission, whether an elevated session is required -- and the estate's
 * standing rule keeps configurable wiring in configuration and compiler
 * passes rather than in attributes. One grep for the tag lists every exposure
 * an installation has.
 *
 * !! Nothing here is a contract the module implements, and nothing in a core
 * package reads this tag. Whichever module offers a terminal collects it.
 */
final class ConsoleExposure
{
    /**
     * The tag a module puts on a command service to offer it.
     *
     * !! Named for the capability (a console command is exposed), not for the
     * Terminal module that happens to collect it today.
     */
    public const string TAG = 'coolms.console.exposed';

    /**
     * The name the person types. Required.
     *
     * Not the console name: `ls`, not `coolms:vfs:ls`. A terminal is a shell,
     * and its verbs are shell verbs.
     */
    public const string NAME = 'name';

    /**
     * A permission id or role, asked of the authorization layer before the
     * command runs. Required -- an exposure without one is refused at compile
     * time, because "the terminal is admin-only" is not a permission and has
     * never been one.
     */
    public const string PERMISSION = 'permission';

    /**
     * True when the run also needs an ELEVATED session, asked after the
     * permission check.
     *
     * Belonging to a group that carries an administrator's role is not the
     * same as acting as one: a member holding the role is still refused, by
     * name, so the client can ask them to elevate and send the line again.
     * Defaults to false; required true where the permission is an
     * administrator's.
     */
    public const string ELEVATION = 'elevation';

    /**
     * True when the command is too long for a request and must run in a
     * worker, streaming its output to a channel. Defaults to false.
     */
    public const string BACKGROUND = 'background';

    /**
     * True when the command needs the elevated session for its WHOLE length,
     * not only at submission -- so a background run whose window closes
     * mid-way stops rather than finishing unelevated. Defaults to false.
     */
    public const string ELEVATION_THROUGHOUT = 'elevation_throughout';

    /**
     * How long a run typically takes, in seconds. Read at submission: when
     * {@see ELEVATION_THROUGHOUT} is set and the session's remaining elevation
     * is shorter than this, the person is warned before the run starts rather
     * than after it stops half-done. Optional.
     */
    public const string TYPICAL_SECONDS = 'typical_seconds';

    private function __construct()
    {
        // Constants only; no instances.
    }
}
