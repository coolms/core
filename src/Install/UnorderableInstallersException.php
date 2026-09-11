<?php

declare(strict_types=1);

namespace CoolMS\Core\Install;

use RuntimeException;

/**
 * The installers cannot be put in an order, and nothing has run.
 *
 * !! THE TIMING IS THE WHOLE POINT. The failure this replaces was
 * `DecisionVfsInstaller` discovering at line 76, part-way through an
 * installation, that no `root` system user existed -- after the secret store had
 * already been written and with six installers still to go. "Nothing provides X"
 * is knowable before the first installer executes, so it is reported there.
 *
 * Carries the findings rather than only a message, so a caller can render them
 * however it likes and a test can assert on them without parsing prose.
 */
final class UnorderableInstallersException extends RuntimeException
{
    /**
     * @param array<string, list<class-string>> $unsatisfied token => installers that asked for it
     * @param list<class-string>                $cycle       installers in a dependency cycle
     */
    public function __construct(
        public readonly array $unsatisfied,
        public readonly array $cycle,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * @param array<string, list<class-string>> $unsatisfied
     */
    public static function unsatisfied(array $unsatisfied): self
    {
        $lines = [];
        foreach ($unsatisfied as $token => $askers) {
            $lines[] = sprintf('  nothing provides "%s", required by: %s', $token, implode(', ', $askers));
        }

        return new self(
            $unsatisfied,
            [],
            "Install refused before anything ran -- a prerequisite nothing provides:\n".implode("\n", $lines),
        );
    }

    /**
     * @param list<class-string> $cycle
     */
    public static function cycle(array $cycle): self
    {
        // Naming every member, not just the first: a cycle has no head, and a
        // message naming one installer sends the reader to fix the wrong end of
        // it. Sorted so the same cycle reads the same way twice.
        $named = $cycle;
        sort($named);

        return new self(
            [],
            $named,
            sprintf(
                "Install refused before anything ran -- these installers require each other in a cycle:\n  %s",
                implode("\n  ", $named),
            ),
        );
    }
}
