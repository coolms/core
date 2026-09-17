<?php

declare(strict_types=1);

namespace CoolMS\Core\Health;

/**
 * A module-published seam for ONE long-running dependency, and the question that
 * finds out whether it is alive.
 *
 * It lives here, beside {@see \CoolMS\Core\Retention\RetentionPrunerInterface} and
 * {@see \CoolMS\Core\Outbox\OutboxBacklogInterface}, because liveness is a
 * declaration a module makes TO the platform, exactly as retention and backup are.
 * The module that owns a dependency writes its probe -- only it knows what a
 * meaningful question is -- and the platform owns the collecting, the reporting and
 * the number that should be zero. Implementers are collected by tag
 * `coolms.diagnostics.probe` (registered in the Core Extension) into
 * {@see \CoolMS\Core\Application\Health\LivenessRunner}, behind `coolms:doctor`.
 *
 * **Why it exists.** Centrifugo was dead for nine hours in September 2026 while the
 * container lint, the smoke check, the whole unit suite and the commit gate stayed
 * green: every one of them reads what the CODE says, and none asks anything of what
 * is RUNNING. Realtime was dropping every event estate-wide and no gate could have
 * noticed, because each publish path is a never-throw seam.
 *
 * ## What a probe must do
 *
 * **Ask something that fails when the thing is down.** Reading configuration,
 * confirming a service is wired, or checking that a class exists all stay true while
 * the dependency is a corpse. Where the only available signal cannot distinguish
 * alive from dead, say so in the state's detail rather than returning a green row
 * that means nothing.
 *
 * ## What a probe must NEVER do
 *
 * - **No writes.** A probe observes. It does not create, update or delete domain
 *   data, enqueue real work, or send anything a person receives. The one sanctioned
 *   exception is a write whose only reader is the probe itself -- a publish to a
 *   throwaway channel nothing subscribes to, a cache key it then reads back -- and
 *   such a write carries a per-run random suffix so two concurrent doctors cannot
 *   read each other's trace as their own answer.
 * - **No side effects a user could observe.** This runs from an operator's terminal
 *   and, through the session hook, unattended. A probe that charges, emails, rotates
 *   a credential or marks something read is a defect however useful its answer.
 * - **No unbounded wait.** Every probe bounds its own timeout. The caller cannot: a
 *   probe that can hang is a probe that will, and it takes the whole report with it.
 * - **No throwing, preferably.** The runner catches anyway, so one broken probe never
 *   blinds an operator to the other seven, but a probe that converts its own failure
 *   into {@see DependencyState::silent()} can say something useful about why.
 *
 * ## Unreachable is not the same as absent
 *
 * Two different facts, and conflating them buries the one that matters:
 *
 * - The installation DECLARES the dependency and it did not answer ->
 *   {@see DependencyState::silent()}. This is a fault, and it is what the
 *   zero-number counts.
 * - The installation does not declare it at all (no DSN, no host, the feature is off)
 *   -> {@see DependencyState::notConfigured()}. Nothing was asked, so nothing is
 *   broken; it reports `absent`, never `ok`.
 *
 * A probe therefore checks for configuration FIRST and returns `notConfigured()`
 * without attempting the ask. An installation that runs no search index is not a
 * broken installation, and reporting it as one teaches operators to ignore red rows.
 *
 * ## One method, and it stays one method
 *
 * This is a published package, so this shape is a compatibility promise from here on:
 * adding a method breaks every implementer, in every installation, on upgrade.
 *
 * It therefore ships with exactly ONE method and no default implementation. Anything
 * a caller needs to know -- the dependency's name, whether it is required, when it
 * last did something, what was asked -- travels in the returned {@see DependencyState}
 * rather than as further methods here. That is deliberate: a value object can gain an
 * optional constructor parameter or a new named constructor without breaking a single
 * implementer, while an interface cannot gain anything at all. Where this seam needs
 * to grow, it grows in the state, not in the contract.
 */
interface LivenessProbeInterface
{
    /**
     * Ask the dependency, and report what it said.
     *
     * Bound your own wait; never write anything a person could observe.
     */
    public function check(): DependencyState;
}
