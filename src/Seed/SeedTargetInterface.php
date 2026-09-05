<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

/**
 * Where seeded content is read from and written to.
 *
 * A port, so the seeding RULE can be unit-tested without a database, an HTTP
 * client or a VFS -- and so the same seeder works against whatever the platform
 * stores a page as, rather than knowing.
 *
 * ⚠️ The body is whatever the platform already stores, not a theme-specific
 * format. The acceptance test for an implementation is a round trip: seed a
 * page, open it in the admin, change one word, save. If the editor cannot open
 * what was seeded, the seeder has created second-class content.
 */
interface SeedTargetInterface
{
    /**
     * The stored body, or null when nothing is there.
     */
    public function readBody(string $path, string $locale): ?string;

    /**
     * The artefact's stored extras, or null when the PATH IS FREE.
     *
     * ⚠️ This doubles as the occupancy answer, so the distinction between null
     * and an empty array carries weight: null means nothing is at this path at
     * all, `[]` means something is there and holds no extras. An implementation
     * that returns null for a node it merely failed to read turns
     * {@see SeedDecision::RefuseOccupied} into a silent overwrite.
     *
     * @return array<string, mixed>|null
     */
    public function readExtras(string $path): ?array;

    /**
     * Write the body and merge `$extras`.
     *
     * ⚠️ MERGE, never replace: the extras bag holds other seeders' markers and
     * whatever the editor has set, and a seeder that replaces it destroys both.
     *
     * @param array<string, mixed> $extras
     */
    public function write(string $path, string $locale, string $body, array $extras): void;
}
