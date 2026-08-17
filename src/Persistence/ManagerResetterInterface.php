<?php

declare(strict_types=1);

namespace CoolMS\Core\Persistence;

/**
 * Reset the default ORM entity manager — discard the current instance and have the
 * registry re-create a fresh, OPEN one (the same primitive Symfony Messenger's
 * `reset_on_message` uses between messages).
 *
 * The F7 outbox relay uses this for broker-like per-message isolation: a
 * consumer whose work throws inside its own transaction CLOSES the EntityManager
 * (the ORM closes it deliberately — a rolled-back unit of work may
 * be inconsistent), and an already-closed EM would make every SUBSEQUENT row in the
 * relay batch fail with `EntityManagerClosed`. Resetting after each failed row
 * un-poisons the EM so the next message starts clean. The relay's own bookkeeping
 * (claim / mark) is raw DBAL on the connection, so it is unaffected by the reset.
 *
 * A Core L0 port so the Core Application relay never imports the ORM
 * (`CoolmsArchitectureRule` fences the ORM out of every layer but Infrastructure),
 * mirroring {@see \CoolMS\Core\Transaction\TransactionRunnerInterface}.
 */
interface ManagerResetterInterface
{
    public function reset(): void;
}
