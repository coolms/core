<?php

declare(strict_types=1);

namespace CoolMS\Core\Worker;

/**
 * One signal that asks every long-running worker to finish what it is doing and leave: a worker that outlives the
 * build it booted from loads its services from files that are gone, and dies on its next lazy load.
 *
 * Ordered by a counter, never a clock: a request advances a generation; a worker records the generation it started in
 * and leaves once the generation has moved past it. A clock that steps back would let a worker started after a
 * request leave for it, and one started before stay.
 *
 * **Every long-running worker must obey it.** A worker that never asks {@see requestedSince()} outlives its build.
 */
interface WorkerRestartSignalInterface
{
    /** Ask every worker running now to finish what it is doing and exit. */
    public function request(): void;

    /** The signal's generation now: a worker records it when it starts, after its boot. */
    public function generation(): int;

    /**
     * Whether a restart was asked for since the worker recorded `$generation`. Cheap enough for a tight loop: an
     * implementation reads its store at most once per interval, and a yes stays a yes.
     */
    public function requestedSince(int $generation): bool;
}
