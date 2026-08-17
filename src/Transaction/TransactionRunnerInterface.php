<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

/**
 * Cross-module transactional seam. Application-layer orchestrators
 * (the workflow module's deployer was the first) that
 * must coordinate multiple Domain repository writes in an
 * all-or-nothing fashion depend on this contract instead of importing
 * an ORM `EntityManagerInterface` directly -- the latter is fenced
 * out of Application code by `CoolmsArchitectureRule`.
 *
 * **Why not push transactions into the repositories?** The unit of
 * work spans more than one aggregate (e.g. the workflow deployer mints
 * a workflow definition version,
 * advances its parent definition's
 * pointer, AND calls into VFS to mint a Node row -- three independent
 * Domain seams). A "save these N entities atomically" repository
 * method would couple the repos to each other. The runner inverts the
 * dependency: the orchestrator owns the work-list, the runner owns
 * the transactional wrap.
 *
 * **Implementation contract:**
 *  - {@see run} opens a transaction, invokes `$work()`, commits, and
 *    returns the callable's return value. Any throwable propagates
 *    after a rollback.
 *  - {@see flush} pushes pending unit-of-work changes to the DB
 *    inside the current transaction without committing it. Required
 *    by orchestrators that need the domain-event publisher to
 *    dispatch their recorded events at a deterministic moment
 *    (events fire on postFlush, not on transaction commit).
 *
 * Nested invocations of {@see run} use savepoint semantics on
 * Postgres / MySQL (the ORM's default behaviour); SQLite is
 * effectively flat-only but every consumer here is server-DB-only.
 */
interface TransactionRunnerInterface
{
    /**
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public function run(callable $work): mixed;

    /**
     * Flush pending unit-of-work writes inside the current
     * transaction. SHOULD be called from inside a {@see run} callback;
     * calling outside a transaction still flushes (the ORM
     * default) but loses the rollback safety net.
     */
    public function flush(): void;
}
