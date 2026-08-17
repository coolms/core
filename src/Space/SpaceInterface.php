<?php

declare(strict_types=1);

namespace CoolMS\Core\Space;

/**
 * Common contract for a "space" -- a runtime-projected, user-scoped
 * subtree exposed in a Library's accordion shell (Personal home, Shared,
 * one per active site section).
 *
 * Spaces are value objects, never persisted: each request materialises
 * its own set from VFS Nodes + SiteSection rows + group membership.
 *
 * Consumed today by the Media Library (`coolms.media.space_provider`
 * tag) and Document Library (`coolms.document.space_provider` tag).
 * Each module wires its own {@see SpaceProviderInterface} pipeline so
 * the surfaces stay independent at the API and DI level, while sharing
 * this narrow contract for sort + projection.
 */
interface SpaceInterface
{
    /** Stable per-user identifier, e.g. `personal`, `shared`, `site:default`. */
    public string $key {
        get;
    }

    /** Human-readable accordion header. */
    public string $label {
        get;
    }

    /** VFS root path the space scopes its Library to. */
    public string $rootPath {
        get;
    }

    /**
     * Optional badge text shown next to the label
     * (e.g. `editor` when the user is in an editors group).
     */
    public ?string $badge {
        get;
    }

    /** Whether the requesting user can write to `rootPath`. */
    public bool $isWritable {
        get;
    }

    /**
     * Sort key; ascending. Personal=10, Shared=20, sites=30+.
     * Ties broken by `key` lexically.
     */
    public int $priority {
        get;
    }
}
