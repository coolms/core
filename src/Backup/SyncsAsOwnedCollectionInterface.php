<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * Opt-in declaration: "this table of mine is not a set of independent rows — it
 * is a COLLECTION OWNED by another row, and the change-feed should treat it as one."
 * Most contributors say nothing; the {@see ReconcilesDeletesInterface} /
 * {@see DefersRestoreColumnsInterface} pattern.
 *
 * **What it exists for.** A many-to-many join table (`coolms_identity_user_groups`)
 * has a COMPOSITE primary key and no entity class, so it fits neither half of the feed's
 * `(table, row_id, op)` contract: `row_id` is one varchar(64) — two RFC4122 UUIDs need 73 —
 * and the UnitOfWork never schedules an entity for it, so capture never sees the row.
 *
 * **The reframing this interface encodes.** A membership row is not independently
 * meaningful; it is one element of `User::$groups`. So the feed keys the table by its
 * OWNER — `(user_groups, <user_id>, upsert)` meaning **"this owner's set changed, re-read
 * it"** — and the applier replaces the owner's whole set. That is what makes the whole
 * thing work with no schema change (one owner UUID is 36 chars) and, more importantly, it
 * is the only formulation that survives `clear()`: an ORM-managed collection
 * re-snapshots itself synchronously right after emptying, so by flush time the
 * removed members are **unrecoverable** — no design can name WHICH rows went. Naming the
 * owner instead needs no such knowledge.
 *
 * **The cost, stated plainly:** for these tables `row_id` holds an OWNER id, not a row id
 * — the one place the feed's column name lies. Both readers of the key derive it from
 * here ({@see \CoolMS\CoreModule\Backup\BackupTableRegistry::ownerColumnFor()}) rather
 * than assuming `id`, so the two ends cannot drift.
 */
interface SyncsAsOwnedCollectionInterface
{
    /**
     * Owned-collection tables → the column naming the owning row.
     *
     * Only tables also listed in {@see BackupContributorInterface::tables()} count; the
     * registry ignores anything outside the synced universe.
     *
     * @return array<string, string> e.g. `['coolms_identity_user_groups' => 'user_id']`
     */
    public function ownedCollectionTables(): array;
}
