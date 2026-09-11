<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

/**
 * Is this user ELEVATED right now -- in the session the current request belongs
 * to -- as opposed to being a MEMBER of the administrators' group?
 *
 * Membership is who you are: permanent, granted by an administrator, inherited
 * down the grant graph, {@see UserInterface::$isAdmin}. Elevation is what state
 * you are in: temporary, self-obtained by proving the admin credential, bound to
 * one login session, expiring on its own. Membership decides what you can SEE
 * and where you can GO; elevation decides what you may DO that the mode bits
 * forbid.
 *
 * !! Lives in Core (L0) beside {@see UserInterface} for the same reason
 * {@see UserGroupResolverInterface} does: the consumers -- the VFS permission
 * check, the terminal dispatcher, the media and font gates -- sit at L1-L3 and
 * must type against it without importing UP into Identity, which owns the
 * implementation and the record.
 *
 * Implementations may cache for their own lifetime, which in a request/response
 * process means "for this request": an elevation granted or dropped mid-request
 * is not guaranteed to be seen by a resolver that already answered.
 */
interface ElevationInterface
{
    /**
     * True only while a grant for THIS user in THIS request's session is live:
     * not revoked, not expired. Membership is not consulted; a member of the
     * administrators' group who has not elevated is not elevated.
     */
    public function isElevated(UserInterface $user): bool;
}
