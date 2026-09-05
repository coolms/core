<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

/**
 * What a seeding run should do with one artefact.
 *
 * The distinction that matters is between {@see self::SkipUnchanged} and
 * {@see self::RefuseEdited}: both leave the artefact alone, and only one of them
 * is a problem the operator needs to hear about.
 */
enum SeedDecision: string
{
    /** Nothing is there yet. Write it. */
    case Create = 'create';

    /**
     * It is there and is byte-for-byte what this seeder last wrote, so nobody
     * has touched it and overwriting loses nothing.
     */
    case Overwrite = 'overwrite';

    /** It is there, unedited, and the incoming bytes are identical. A no-op. */
    case SkipUnchanged = 'skip-unchanged';

    /**
     * ⚠️ Somebody has edited it since the seeder wrote it -- or there is no
     * marker to prove otherwise. Their work wins; this run must not touch it.
     */
    case RefuseEdited = 'refuse-edited';

    /**
     * ⚠️ Something already occupies this path that this seeder did not create.
     *
     * A DIFFERENT situation from {@see self::RefuseEdited}, and the operator's
     * next move differs: an edit is theirs to keep, an occupied path means the
     * seeded page needs somewhere else to go -- or the thing already there needs
     * moving. Folding the two together would report "edited" about a page this
     * seeder has never written, which sends the operator looking for a change
     * nobody made.
     */
    case RefuseOccupied = 'refuse-occupied';

    /**
     * `--force` was given and the artefact was edited. The write happens, and
     * ⚠️ THE CALLER MUST NAME IT: discarding somebody's work silently behind a
     * flag is the failure this whole class exists to prevent.
     */
    case ForcedOverwrite = 'forced-overwrite';

    /** Does this decision write? */
    public function writes(): bool
    {
        return self::Create === $this || self::Overwrite === $this || self::ForcedOverwrite === $this;
    }

    /** Does the operator need to be told, by name, what happened here? */
    public function needsReporting(): bool
    {
        return self::RefuseEdited === $this
            || self::RefuseOccupied === $this
            || self::ForcedOverwrite === $this;
    }
}
