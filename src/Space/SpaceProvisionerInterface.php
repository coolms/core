<?php

declare(strict_types=1);

namespace CoolMS\Core\Space;

/**
 * What a module creates when one of its spaces is enabled.
 *
 * The half that genuinely differs per module: Documents wants a directory with
 * an owner and a mode, Media wants a collection root, Calendars may want
 * neither. Everything else -- the flag, the query, the "which sites can I still
 * add" list -- is shared in {@see ModuleSpaceSettings}.
 *
 * ## The two actions are one action
 *
 * ⚠️ Enabling is **turn the setting on and provision**, in one step, because a
 * flag without its directory is the dead end this replaced: the space appears,
 * the screen navigates into it, and the directory is not there. Neither half
 * alone is a usable state, so neither half is separately exposed.
 *
 * ## Disabling removes, it does not purge
 *
 * ⚠️ {@see deprovision()} exists for symmetry of the listing, not of the data.
 * Turning a space off hides it and **deletes nothing** -- somebody's documents
 * are not a UI preference, and a toggle that destroys content is a toggle
 * nobody can safely use. An implementation that deletes is wrong even though
 * the name would allow it.
 */
interface SpaceProvisionerInterface
{
    /**
     * The settings block whose {@see ModuleSpaceSettings::KEY} governs this
     * module's spaces, e.g. `document.spaces`.
     */
    public function settingsKey(): string;

    /**
     * Create whatever this module needs for `$siteSlug`, with the right owner
     * and mode.
     *
     * ⚠️ Must be idempotent: enabling a space whose directory already exists --
     * left behind by an earlier enable, or made by hand -- is a normal case and
     * not an error.
     */
    public function provision(string $siteSlug): void;

    /**
     * Called when a space is turned off.
     *
     * ⚠️ Remove, not purge. Implementations must leave content alone; the space
     * simply stops being listed. Provided so a module can drop a cache or a
     * derived index, not so it can delete somebody's work.
     */
    public function deprovision(string $siteSlug): void;
}
