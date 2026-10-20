<?php

declare(strict_types=1);

namespace CoolMS\Core\Transaction;

/**
 * Writes a record that must outlive a refusal: the evidence of a rejected webhook, a refused elevation, a replayed
 * token's revocation, a failed run. It writes on a connection of its own, in a short transaction of its own, committed
 * before it returns -- so the unit of work around it, discarded because the request was refused, does not take the
 * record with it.
 *
 * Only for records whose purpose is to survive the failure they describe. Everything else is the unit's.
 */
interface FailurePathWriterInterface
{
    /**
     * Stores a NEW entity now, committed, whatever the surrounding unit of work does later. The entity is not managed
     * by the unit's own persistence afterwards.
     */
    public function persist(object $entity): void;
}
