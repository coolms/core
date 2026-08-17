<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

/**
 * A command asking the SESSION to move to a new working directory.
 *
 * Yielded into the output stream rather than returned, because a command's
 * contract here is a generator of things to emit — but it is a VO, not a
 * string, so the terminal module's execute processor
 * can tell it apart by TYPE and turn it into a `{"cwd": …}` SSE event.
 *
 * The alternative — a magic prefix on a normal output line — would collide the
 * day a command legitimately printed that prefix, and the collision would
 * silently teleport the operator somewhere rather than print a line.
 *
 * The directory itself is not stored server-side: the client holds it and sends
 * it with the next command. That keeps the terminal stateless per request (no
 * session affinity, nothing to expire) and makes two browser tabs two
 * independent shells, which is what a person expects of two terminals.
 */
final readonly class ChangeDirectory
{
    public function __construct(
        /** Absolute, already validated and normalised by the command. */
        public string $path,
    ) {
    }
}
