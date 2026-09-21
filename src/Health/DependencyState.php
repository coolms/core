<?php

declare(strict_types=1);

namespace CoolMS\Core\Health;

use DateTimeImmutable;

/**
 * What one long-running dependency answered when it was ASKED.
 *
 * **"Configured" is never the answer.** A dependency can be declared, wired, compiled
 * and injected while being a corpse -- that is precisely how a realtime node stayed dead
 * for nine hours behind green gates. So `configured` and `answered` are carried as
 * two separate facts, deliberately, so that no reader and no future refactor can take
 * one for the other. A state is only {@see answered()} when a probe put a real
 * question to the thing and got a real reply.
 *
 * `$ask` carries the question ITSELF -- the statement, the URL, the command -- beside
 * the answer, so a red row tells its reader what was tried without opening the probe.
 *
 * `$lastActivityAt` is the last time the dependency is known to have DONE something,
 * where a record exists to read. Null means "nothing records this", never "never":
 * an absent record and an idle dependency must not read alike.
 *
 * This is the growth point of the seam. {@see LivenessProbeInterface} is a published
 * one-method contract that cannot gain members without breaking every implementer;
 * this value object can gain an optional parameter or a new named constructor without
 * breaking any. New facts about a dependency belong here.
 */
final readonly class DependencyState
{
    private function __construct(
        /** How an operator refers to it ("Search index"). */
        public string $name,
        /** The question put to it, verbatim enough to repeat by hand. */
        public string $ask,
        /** What came back, or why nothing did. */
        public string $detail,
        /** A required dependency that was asked and stayed silent is a failure. */
        public bool $required,
        /** The installation declares it: a DSN, a host, a row. */
        public bool $configured,
        /** It was asked, and it replied. */
        public bool $answered,
        /** Last known activity; null when nothing records it. */
        public ?DateTimeImmutable $lastActivityAt = null,
        /** It was asked, and the answer could not distinguish alive from dead. */
        public bool $inconclusive = false,
    ) {
    }

    /** It was asked and it replied. */
    public static function answered(
        string $name,
        string $ask,
        string $detail,
        bool $required = true,
        ?DateTimeImmutable $lastActivityAt = null,
    ): self {
        return new self($name, $ask, $detail, $required, true, true, $lastActivityAt);
    }

    /**
     * It is configured, it was asked, and it did not answer. This is the state the
     * whole command exists to surface.
     */
    public static function silent(
        string $name,
        string $ask,
        string $detail,
        bool $required = true,
        ?DateTimeImmutable $lastActivityAt = null,
    ): self {
        return new self($name, $ask, $detail, $required, true, false, $lastActivityAt);
    }

    /**
     * The installation does not declare it, so there was nothing to ask. NOT a
     * failure even when the dependency would be required if present: an installation
     * that runs no search index is not a broken installation, and reporting it as one
     * teaches an operator to ignore red rows.
     */
    public static function notConfigured(string $name, string $ask, string $detail, bool $required = false): self
    {
        return new self($name, $ask, $detail, $required, false, false);
    }

    /**
     * It was asked, and the answer cannot tell alive from dead.
     *
     * The fourth state, and it earns its place: an outbox relay with zero unpublished
     * rows, or a worker heartbeat nothing has dispatched, produce a question whose
     * answer is compatible with a healthy dependency AND a dead one. Reporting that as
     * `ok` is the empty-queue trap -- the exact reasoning that makes queue depth
     * useless for liveness -- and reporting it as `DOWN` cries wolf at an idle system
     * until an operator stops reading the rows.
     *
     * So it is neither: `unknown`, never counted, and the detail says WHY the question
     * could not decide. A doctor is allowed to admit that it does not know; what it may
     * not do is claim health it has not established.
     */
    public static function inconclusive(
        string $name,
        string $ask,
        string $detail,
        bool $required = true,
        ?DateTimeImmutable $lastActivityAt = null,
    ): self {
        return new self($name, $ask, $detail, $required, true, false, $lastActivityAt, true);
    }

    /**
     * The number that should be zero counts exactly these: asked, and silent. An
     * inconclusive answer is not a failure -- an idle system must not hold the number
     * above zero forever, or the number stops meaning anything.
     */
    public function isFailing(): bool
    {
        return $this->required && $this->configured && !$this->answered && !$this->inconclusive;
    }

    /** `ok` / `DOWN` / `unknown` / `absent` -- the four states an operator scans for. */
    public function status(): string
    {
        return match (true) {
            !$this->configured => 'absent',
            $this->answered => 'ok',
            $this->inconclusive => 'unknown',
            default => 'DOWN',
        };
    }
}
