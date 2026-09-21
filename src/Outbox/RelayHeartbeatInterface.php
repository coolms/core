<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

/**
 * Where the relay leaves its heartbeat and a monitor reads it back.
 *
 * The relay and the monitor run in different processes, usually in different
 * containers, so the implementation has to be something both can reach (a
 * shared cache, a table); a process-local store answers "never" to every
 * monitor. One beat is kept, the latest, overwritten each pass: nothing
 * accumulates and there is nothing to prune.
 */
interface RelayHeartbeatInterface
{
    public function beat(RelayHeartbeat $heartbeat): void;

    /**
     * The latest beat, or null when none was recorded -- which a monitor
     * reads as "not running", since a running relay beats every pass.
     */
    public function last(): ?RelayHeartbeat;
}
