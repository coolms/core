<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

/**
 * The one question every privileged gate asks once its ORDINARY check has
 * refused: may this user bypass the check here, now?
 *
 * Before this port each gate read {@see UserInterface::$isAdmin} for itself --
 * seven one-line checks, none of them counted, all of them meaning "member of
 * the administrators' group", and all of them asked FIRST, before the mode bits
 * or the role, so nobody could tell whether the bypass had been needed. This
 * port puts the question in one place and after the ordinary check, so that
 * the answer can change from membership to {@see ElevationInterface elevation}
 * in one place, and -- the reason it is a port rather than a boolean -- so that
 * the change can be REHEARSED: a log-only implementation keeps answering with
 * membership while recording, for every refusal it overrode, that elevation
 * would not have.
 *
 * !! A GATE MUST NOT BE ABLE TO BE SILENT. A recording implementation counts
 * EVALUATIONS beside would-refuse HITS, per gate. An evaluation is every time
 * the gate's decision point was reached -- {@see passedWithoutBypass()} when the
 * ordinary check allowed, {@see mayBypass()} when it refused -- so that "0
 * bypasses observed" and "the gate never ran" are different outputs. A hit is a
 * `mayBypass()` where membership bypassed and elevation would have refused:
 * exactly "reached only because of the bypass", which is what the baseline
 * counted.
 *
 * `$gate` names the call site (`vfs.permission`, `vfs.delete_sticky`,
 * `vfs.chown`, `vfs.chmod`, `terminal.role`, `media.listing`, `font.install`);
 * `$path` and `$permission` are what the gate was deciding about, for the
 * shape-by-subtree comparison, and may be null where there is no node.
 */
interface ElevationGateInterface
{
    /** The ordinary check refused. May this user bypass it here? */
    public function mayBypass(string $gate, UserInterface $user, ?string $path = null, ?string $permission = null): bool;

    /** The ordinary check allowed; no bypass was needed. Counted, so the gate is never silent. */
    public function passedWithoutBypass(string $gate): void;
}
