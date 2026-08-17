<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * The per-SiteSection selective-sync axis — the seam a module
 * implements when SOME of its synced tables partition by site section, so a
 * sections-scoped edge receives only the `/content/<slug>` subtrees it is scoped
 * to. Collected by tag (`coolms.sync.section_partition`), the
 * {@see SyncBlobContributorInterface} pattern: Core owns the seam, the module
 * that owns the partition CONVENTION implements it (today: Section, the one
 * place that knows the `/content/` namespace), and the sync surface consults it
 * without importing either.
 *
 * Section keys are opaque strings to Core — site-section slugs today. Tables
 * NOT named by any implementor have no section axis and are never filtered by
 * a `sections` scope (they remain governed by tiers alone).
 *
 * **Fail direction, by method:** `filterRows` and `blobInScope` guard DATA and
 * fail CLOSED (an unresolvable row/hash is withheld); `outOfScopeIds` guards
 * lean feed POINTERS and returns only ids PROVEN out of scope — an id it cannot
 * resolve (typically a just-deleted row, whose path no longer exists) is NOT
 * out of scope, because dropping its delete delta would strand the row on edges
 * that legitimately hold it, while delivering it to an edge that never had the
 * row is a harmless no-op delete.
 */
interface SyncSectionPartitionInterface
{
    /**
     * The synced tables this implementor partitions by section.
     *
     * @return list<string>
     */
    public function partitionedTables(): array;

    /**
     * Keep only the rows of `$table` that fall inside `$sections` (plus rows
     * with no section at all — shared trees outside the partition namespace).
     *
     * @param list<array<string, mixed>> $rows     rows in backup-export shape
     * @param list<string>               $sections allowed section keys
     *
     * @return list<array<string, mixed>>
     */
    public function filterRows(string $table, array $rows, array $sections): array;

    /**
     * The subset of `$ids` PROVEN to live outside `$sections`. Unresolvable
     * ids are not included (see the interface docblock's fail-direction note).
     *
     * @param list<string> $ids      row UUIDs as emitted by the change feed
     * @param list<string> $sections
     *
     * @return list<string>
     */
    public function outOfScopeIds(string $table, array $ids, array $sections): array;

    /**
     * May an edge scoped to `$sections` fetch the bytes behind `$hash`?
     * True iff at least one in-scope (or section-less) row references it.
     *
     * @param list<string> $sections
     */
    public function blobInScope(string $hash, array $sections): bool;
}
