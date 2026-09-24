<?php

declare(strict_types=1);

namespace CoolMS\Core\Retention;

/**
 * Optional companion to {@see RetentionPrunerInterface}: a pruner that can count
 * the population its expired rows are drawn from.
 *
 * A report says "N of M" only when M is known. A pruner that implements this
 * gives M; one that does not is reported "N of unknown" -- UNEVALUABLE, never a
 * zero, because a count nobody took is not a count of nothing.
 *
 * Additive on purpose: RetentionPrunerInterface is unchanged, so no implementer
 * breaks, and a pruner that cannot count says so by not implementing this.
 */
interface RetentionPopulationInterface
{
    /**
     * Every row the pruner's expiry rule is asked of -- expired or not -- in the
     * same terms as {@see RetentionPrunerInterface::countExpired()}.
     */
    public function countPopulation(): int;
}
