<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

/**
 * A CONNECTION-level transactional seam — the sibling of
 * {@see TransactionRunnerInterface}, but bound to the DBAL connection rather than
 * the ORM EntityManager.
 *
 * **Why a separate port?** {@see TransactionRunnerInterface} wraps the work in
 * `EntityManagerInterface::wrapInTransaction`, whose final commit flushes the EM. If
 * the work (or anything it calls) CLOSES the EM — the ORM does exactly this when a
 * unit of work rolls back, as a consistency safeguard — that final flush throws
 * `EntityManagerClosed` and the whole transaction aborts.
 *
 * The F7 outbox relay needs a batch transaction that survives a consumer
 * closing the EM mid-batch: its own bookkeeping (claim + mark-published/failed) is
 * raw DBAL on the connection, so wrapping the batch at the CONNECTION level keeps the
 * commit independent of EM state. Paired with {@see \CoolMS\Core\Persistence\ManagerResetterInterface}
 * (reset the closed EM per failed row), this gives broker-like per-message isolation.
 *
 * A Core L0 port so Application/Console code never imports the ORM
 * (`CoolmsArchitectureRule` fences the ORM and its connection layer out of
 * everything but the persistence adapter).
 */
interface ConnectionTransactionRunnerInterface
{
    /**
     * Open a connection-level transaction, invoke `$work()`, commit, and return its
     * value. Any throwable propagates after a rollback.
     *
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public function run(callable $work): mixed;
}
