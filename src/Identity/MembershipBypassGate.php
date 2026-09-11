<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

/**
 * The gate as it was before elevation existed: membership of the administrators' group
 * bypasses, and nothing is counted.
 *
 * !! THIS IS THE SILENT ONE, AND IT IS THE DEFAULT ON PURPOSE -- for the same
 * reason `PermissionService` defaults its group resolver: a dozen hand-built
 * `new PermissionService($nodes)` fixtures must keep constructing, and a default
 * that degrades to the OLD behaviour degrades to slow-or-permissive-as-before,
 * never to a new permission. It is never wired in the container; the
 * application's recording gate is. A test that wants to see the counting must
 * inject that one.
 */
final class MembershipBypassGate implements ElevationGateInterface
{
    public function mayBypass(string $gate, UserInterface $user, ?string $path = null, ?string $permission = null): bool
    {
        return $user->isAdmin;
    }

    public function passedWithoutBypass(string $gate): void
    {
        // silent by construction -- see the class docblock
    }
}
