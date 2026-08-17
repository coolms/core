<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

/**
 * The original membership read: ask the user entity, and let
 * it walk the grant graph through the ORM's lazy associations.
 *
 * This exists so that the authorization sites which take a
 * {@see UserGroupResolverInterface} can hold a non-null one even when nobody
 * wired the fast implementation — a hand-built fixture, a unit test, a service
 * constructed outside the container. It is the SAME answer at ~25 queries
 * instead of two (`UserGroupResolverEquivalenceTest` holds the two equal
 * against a real database), so falling back here is slow, never wrong.
 *
 * ⚠️ **`config/services.yaml` excludes this file from the `App\:` scan, and that
 * line is load-bearing.** Registered, it would be a SECOND service implementing
 * {@see UserGroupResolverInterface} for the alias to choose between — and if it
 * won, every request would quietly go back to paying for the walk with nothing
 * failing to say so. It is only ever `new`ed, as a default argument. (The
 * attribute form, `#[Exclude]`, is not available here: that is a
 * DependencyInjection import and this is a Domain class.)
 */
final class EntityWalkUserGroupResolver implements UserGroupResolverInterface
{
    public function allGroupIdsFor(UserInterface $user): array
    {
        return array_values($user->getAllGroupIds());
    }
}
