<?php

declare(strict_types=1);

namespace CoolMS\Core\Worker;

/**
 * One worker, and when a successful unit of work is expected from it, for whatever reads worker health: "no work
 * while work is expected" is a finding, so each worker says what expected means for it. A worker that consumes a
 * queue fed every minute expects work within minutes; one that holds connections expects it only while there are
 * connections to hold.
 *
 * Implementations are collected by the service tag {@see self::TAG}. A worker without one is not read at all, so
 * every worker that records through {@see WorkerHealthRecorderInterface} declares one.
 */
interface WorkerExpectationInterface
{
    /** The service tag expectations are collected under. */
    public const string TAG = 'coolms.worker_expectation';

    /** The same name the worker records under. */
    public function worker(): string;

    /**
     * Seconds within which a successful unit of work is expected, or null when none is expected now -- then only
     * repeated failure is a finding. It may throw when it cannot tell, and the reader then says it could not
     * evaluate that worker.
     */
    public function expectsWorkWithinSeconds(): ?int;
}
