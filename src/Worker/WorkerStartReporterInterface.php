<?php

declare(strict_types=1);

namespace CoolMS\Core\Worker;

/**
 * Where a long-running worker says it has started, and in which restart generation: once, straight after it records
 * {@see WorkerRestartSignalInterface::generation()}. What it cannot be trusted to know about itself -- the commit its
 * code came from, its process and its host -- the implementation adds.
 *
 * `$worker` is the name its {@see WorkerHealthRecorderInterface} records under. Reporting never throws: a worker that
 * cannot say it started must still start.
 */
interface WorkerStartReporterInterface
{
    public function started(string $worker, int $generation): void;
}
