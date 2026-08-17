<?php

declare(strict_types=1);

namespace CoolMS\Core\Blameable;

use Symfony\Component\Uid\Uuid;

/**
 * Marker interface for any security principal that participates in the blameable system.
 *
 * Implemented by any user type (Identity\User, SSO principal, service account, …).
 * Requires only a UUID identity -- no direct dependency on the Identity module.
 * The Core BlameableEntityEventListener extracts the UUID via this interface.
 */
interface BlameableActorInterface
{
    public Uuid $id {
        get;
    }
}
