<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

use DateTimeImmutable;

/**
 * How long this session's elevation still has to run.
 *
 * ## Why a second port beside {@see ElevationInterface}
 *
 * "Is this person elevated" is enough for a gate that answers now. It is not
 * enough for work that starts now and finishes LATER: a command sent to a
 * worker may still be running when the window closes, and the honest thing is
 * to say so at submission -- "this usually takes four minutes and your
 * elevation has ninety seconds left" -- rather than to stop it half-done.
 *
 * Deliberately narrow: the expiry, and nothing about how elevation was
 * obtained or what it permits. A caller that wants to decide anything else
 * asks the gate.
 */
interface ElevationWindowInterface
{
    /**
     * When the live grant for this person's session expires, or null when
     * there is no live grant.
     */
    public function elevationExpiresAt(UserInterface $user): ?DateTimeImmutable;
}
