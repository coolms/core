<?php

declare(strict_types=1);

namespace CoolMS\Core\Worker;

/**
 * Where a long-running worker says how each unit of its work went: a message handled, a tick, a relay pass, a
 * connection held. What records it -- counting failures per worker, keeping the time of the last success, showing
 * them to an administrator -- is a module's, never core's.
 *
 * `$worker` is a stable name, the one its {@see WorkerExpectationInterface} answers with ("messenger:async",
 * "email-idle", "sms-smsc:main"). Recording never throws: a health signal that cannot be written must not break the
 * work it measures.
 */
interface WorkerHealthRecorderInterface
{
    public function succeeded(string $worker): void;

    /** `$reason` is the failure's message, which the recorder may shorten; never a secret. */
    public function failed(string $worker, string $reason): void;
}
