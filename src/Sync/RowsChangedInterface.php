<?php

declare(strict_types=1);

namespace CoolMS\Core\Sync;

/**
 * An event that says rows of a table changed in a way nothing else can see.
 *
 * A module that writes through the object manager needs this for nothing: the
 * platform watches the unit of work and records what it committed. A module
 * that writes with raw SQL -- a bulk update, a path rewrite -- is invisible
 * there, and says so by dispatching an event that implements this. Whatever
 * keeps a change feed subscribes; the module names no feed, no table of one,
 * and no recorder (2026-09-22).
 */
interface RowsChangedInterface
{
    /** The table whose rows changed, as the database names it. */
    public function changedTable(): string;

    /**
     * The rows, by primary key, as strings.
     *
     * @return list<string>
     */
    public function changedRowIds(): array;

    /** True when the rows were deleted rather than written. */
    public function rowsWereDeleted(): bool;
}
