<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * One kind of value sealed under the master key, as the rotation sweep sees
 * it: a name, and a sweep that reads every value the kind holds and re-seals
 * the ones still under the previous key.
 *
 * A module that stores sealed values implements this once per kind (a
 * column, a file, a set of nodes) and the host collects the implementations;
 * `coolms:secret:rotate` runs them all and prints one tally per kind. The
 * sweep must be idempotent -- a value already under the current key is
 * skipped, not rewritten -- so it can stop and resume, and so a second run
 * re-seals nothing. With `$apply` false it counts and writes nothing.
 */
interface SealedKindInterface
{
    /** A stable, operator-facing name: the table and column, or the file. */
    public function name(): string;

    public function sweep(bool $apply): RotationTally;
}
