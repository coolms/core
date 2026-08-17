<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

use Symfony\Component\Uid\Uuid;

/**
 * Minimal canonical user contract for ownership and permission checks
 * across the platform.
 *
 * Lives in this package (level 0) -- the most foundational tier --
 * so every module (Workflow, VFS, Form, Document, ... at L1-L3) can
 * type its actor parameter against this contract without anyone
 * importing UP into the Identity module. Identity's richer
 * The identity module's own user contract extends this one,
 * adding Symfony Security primitives (password, lastLoginAt, etc.)
 * that only login/auth code needs.
 *
 * **Why Core and not Entity:** Entity (L0) depends on Core (L0) but
 * not the reverse. Until 2026-06-02 a transitional copy of this
 * contract lived at `CoolMS\Core\Identity\UserInterface` -- pragmatic
 * but architecturally wrong because Core's
 * {@see \CoolMS\Core\Space\SpaceProviderInterface} consumed it,
 * forcing an inverted Core -> Entity import. Moving it down to Core
 * removes that inversion and the previously baseline-suppressed
 * "Core -> Identity" Domain-boundary violation in one stroke.
 *
 * Provides identity primitives -- id, group membership, privilege
 * flags -- in shape sufficient for all generic-actor consumers
 * (VFS ACL checks, Workflow system-user lookup, Space provider
 * filtering, etc.). Roles return as a plain `string[]` so consumers
 * needing a `hasRole()` style check do `in_array($role, $u->getRoles(), true)`.
 */
interface UserInterface
{
    public Uuid $id {
        get;
    }
    public Uuid $primaryGroupId {
        get;
    }
    public bool $isRoot {
        get;
    }
    public bool $isSystem {
        get;
    }

    /**
     * All group IDs the user belongs to (primary and supplementary).
     *
     * @return Uuid[]
     */
    public function getAllGroupIds(): array;

    /**
     * @return string[]
     */
    public function getRoles(): array;
}
