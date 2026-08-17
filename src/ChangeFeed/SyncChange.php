<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

use CoolMS\Core\Attribute\ClassMeta;
use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use CoolMS\Core\Model\AggregateRootInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * One row in the controller→edge sync change-feed — the dedicated,
 * append-only CDC log that is NOT the Outbox: `(table, row UUID, op, recorded_at)`.
 * Written by the persistence adapter's change-capture listener, inside the
 * domain write's own transaction and from its change set, so a change
 * and its record commit together or neither does.
 *
 * Capture scope = the backup contributors' tables
 * ({@see \CoolMS\CoreModule\Backup\BackupTableRegistry}, B.2.1) — **now equal to what
 * backup exports.** That claim has been wrong in both directions and the history is
 * worth keeping: it originally read "provably equal", then was narrowed to "their
 * single-`id`, entity-mapped rows" on discovering `coolms_identity_user_groups` was
 * exported but uncapturable. Closing that last table (by keying it on its owner —
 * see below) made scope and export coincide again — this time actually checked. The one
 * remaining divergence is not a table at all: VFS blob BYTES. This is a platform primitive
 * living in Core alongside the Outbox / Inbox event rails.
 *
 * **Ordering / cursor (B.2.3):** each row also carries a monotonic `seq` — a DB-level
 * `BIGINT GENERATED ALWAYS AS IDENTITY`, intentionally UNMAPPED here (like the repo's
 * `v_*` generated columns): the ORM never writes it, and the change-feed reader
 * ({@see SyncChangeFeedReaderInterface}) projects it via DBAL.
 * `seq` is the stable total order an edge paginates through; `recorded_at` (indexed,
 * second precision) stays for retention windows, not as the cursor.
 *
 * **`row_id` is a ROW id — except for an owned collection, where it is an OWNER id.**
 * `coolms_identity_user_groups` (the platform's only JoinTable) has a composite PK and no
 * entity, so it is unrepresentable here as a row: two RFC4122 UUIDs need 73 chars and this
 * column is 64. It was closed WITHOUT widening the column, by keying the table on its
 * owner instead — `(user_groups, <user_id>, upsert)` = "this user's set changed". See
 * {@see \CoolMS\Core\Backup\SyncsAsOwnedCollectionInterface}; every reader of a
 * `row_id` resolves the column via `BackupTableRegistry::ownerColumnFor()`.
 *
 * **NOT captured (remaining gap, with its reason):**
 *  - VFS blob BYTES — not a table gap at all: the node row and its `content_hash` sync
 *    fine, but the sync path has no blob channel (unlike backup's `putBlob`/`readBlob`), so
 *    an edge gets a file with correct metadata and no bytes. Needs a channel, not a fix.
 *
 * The two `coolms_vfs_nodes` UoW-bypassing writes (bulk path-rewrite DQL + raw counter
 * DBAL) are covered by {@see SyncChangeRecorderInterface}.
 *
 * **Retention:** the feed is pruned only once all edges pass a cursor (ADR §3) — a
 * prune-after-cursor pruner ships with the edge-registry slice.
 */
#[ClassMeta(label: 'Sync change')]
class SyncChange implements AggregateRootInterface, IdentifierProviderInterface
{
    use IdentifierProviderTrait {
        IdentifierProviderTrait::__construct as private __identityConstruct;
    }

    /** The change-feed table — single source for the mapping and the DBAL reader. */
    public const string TABLE = 'coolms_sync_changes';

    public function __construct(
        public string $tableName,
        public string $rowId,
        public SyncChangeOp $op,
        public DateTimeImmutable $recordedAt,
    ) {
        $this->__identityConstruct(Uuid::v7());
    }

    public static function upsert(string $tableName, string $rowId, DateTimeImmutable $now): self
    {
        return new self($tableName, $rowId, SyncChangeOp::Upsert, $now);
    }

    public static function delete(string $tableName, string $rowId, DateTimeImmutable $now): self
    {
        return new self($tableName, $rowId, SyncChangeOp::Delete, $now);
    }
}
