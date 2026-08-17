<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

/**
 * The "append to outbox" port — a Core L0 cross-cutting contract, the same
 * shape as the Realtime bus and the analytics sink. A
 * producer depends ONLY on this; the implementation persists the row WITHOUT its
 * own flush, so the append joins the caller's transaction and commits atomically
 * with the domain change.
 *
 * The transport behind the port is swappable: a DB-backed Messenger relay in the
 * monolith today, a broker-backed relay once a module is extracted to a service —
 * the producer's API is unchanged either way.
 */
interface OutboxAppenderInterface
{
    /**
     * Queue `$message` for reliable delivery within the caller's current
     * transaction. Implementations MUST NOT flush independently — the row is
     * committed when the caller commits its domain change.
     */
    public function append(OutboxMessage $message): void;
}
