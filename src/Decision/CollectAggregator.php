<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

/**
 * Optional aggregator on a {@see HitPolicy::Collect} decision table
 * When the hit policy is COLLECT, this attribute folds the
 * list of matching outputs into a single scalar; when absent, the
 * evaluator returns the raw match list. Backing string values match
 * the OMG DMN 1.3 spec letter-case.
 *
 * **Semantics summary (full specs in the evaluator):**
 *  - `Sum`   -- numeric sum across all matching output rows.
 *  - `Min`   -- numeric minimum.
 *  - `Max`   -- numeric maximum.
 *  - `Count` -- count of matching rows (any type, returns int).
 *
 * A non-COLLECT hit policy with an aggregator attribute trips
 * `DMN.UNKNOWN_AGGREGATOR` at parse time. Unknown aggregator strings
 * trip the same code. List mode (no aggregator) is the default; the
 * parser stores `null` to denote "no folding".
 */
enum CollectAggregator: string
{
    case Sum = 'SUM';
    case Min = 'MIN';
    case Max = 'MAX';
    case Count = 'COUNT';
}
