<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

/**
 * The unit of work as application code sees it: one request, one message handler or one console command is one unit,
 * and its OWNER -- never the application -- decides at the end whether it commits or is discarded. A unit that fails
 * leaves nothing behind except what was written through a failure-path writer.
 *
 * Application code may only:
 *  - run work that must be all-or-nothing on its own inside the unit ({@see transactional()}): a nested call is a
 *    savepoint, and a row lock taken inside it is held until the OWNER commits, not until the call returns;
 *  - register work that must happen only if the unit commits ({@see afterCommit()}) -- held in memory, so for
 *    best-effort effects only; an effect that must happen goes through the outbox instead.
 *
 * Beginning, committing and discarding belong to the owners alone: {@see UnitOfWorkOwnerInterface}.
 */
interface UnitOfWorkInterface
{
    /**
     * Runs $work all-or-nothing inside the current unit and returns its result. Outside any open unit (a stream's
     * callback, terminate, a daemon's event) it opens a short unit of its own and commits it before returning.
     * Any throwable rolls back what $work did and propagates.
     *
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public function transactional(callable $work): mixed;

    /**
     * Runs $effect after the OUTERMOST unit commits; it is dropped if the unit is discarded. Outside any open unit it
     * runs at once. In memory: a process that dies between the commit and the effect loses it.
     *
     * @param callable(): void $effect
     */
    public function afterCommit(callable $effect): void;
}
