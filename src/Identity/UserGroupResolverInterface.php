<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

use Symfony\Component\Uid\Uuid;

/**
 * Resolves a user's FULL group membership — their direct groups plus every
 * group whose role those grant, transitively.
 *
 * This is the same answer {@see UserInterface::getAllGroupIds()} gives, at a
 * different cost. The entity walks the grant graph through the ORM's lazy
 * `Group::$childRelations`, which is one `SELECT … WHERE parent_group_id = ?`
 * per group VISITED — measured at 25 of the 39 queries on
 * `GET /tags?limit=1`, and identical on every other authenticated endpoint,
 * because VFS's `ApiResourceAccessListener` authorizes them all through
 * `PermissionService`. An implementation of this port reads the graph ONCE and
 * walks it in memory.
 *
 * ⚠️ It lives in Core (L0) beside {@see UserInterface}, for the reason that
 * contract lives here: consumers at L1-L3 (VFS, Calendar, Inbox, Web) must be
 * able to type against it without importing UP into Identity, which owns the
 * implementation.
 *
 * ⚠️ **Implementations may cache for their own lifetime**, which in a
 * request/response process means "for this request" — the same contract
 * `DynamicRoleHierarchy` already has. A grant edited mid-request is not
 * guaranteed to be seen by a resolver that already answered.
 */
interface UserGroupResolverInterface
{
    /**
     * Every group id this user belongs to: their primary group, their
     * supplementary groups, and every group reachable from those through
     * role-grant edges.
     *
     * Cycle-safe (the graph is not supposed to contain one — the grant service
     * refuses edges that would close a loop — but a resolver that hangs on bad
     * data is a worse failure than one that ignores it).
     *
     * @return list<Uuid> deduplicated; direct memberships first
     */
    public function allGroupIdsFor(UserInterface $user): array;
}
