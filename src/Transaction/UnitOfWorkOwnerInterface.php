<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

/**
 * The unit of work as its OWNER sees it. There are exactly three owners -- the HTTP request, the message handler and
 * the console command -- and only they may hold this interface; application code holds {@see UnitOfWorkInterface}.
 *
 * An owner opens a unit at its start and decides at its end: {@see commit()} on success, {@see discard()} on failure.
 * Opening a unit touches no connection: the database transaction begins lazily, at the first statement that writes or
 * takes a lock, so a unit that only reads issues no BEGIN and no COMMIT.
 */
interface UnitOfWorkOwnerInterface
{
    /**
     * Opens the unit. Nested opens are refused: a unit has one owner.
     *
     * @throws UnitOfWorkException when a unit is already open
     */
    public function begin(): void;

    /**
     * Commits what the unit wrote, then runs its after-commit effects. Nothing written: nothing to commit.
     *
     * @throws UnitOfWorkException when no unit is open, while a flush is running, or when the commit fails (the unit
     *                             is then discarded)
     */
    public function commit(): void;

    /** Rolls back what the unit wrote, drops its after-commit effects and leaves the persistence layer usable. */
    public function discard(): void;

    /** Whether the unit can still commit: false once the persistence layer is closed or marked rollback-only. */
    public function isUsable(): bool;

    /** Whether a unit is open. */
    public function ownerActive(): bool;
}
