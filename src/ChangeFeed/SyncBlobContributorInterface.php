<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * A module's SIDE-CHANNEL BYTES — the data its synced rows REFER to but do not contain
 *. The change feed replicates rows; a row that names bytes (a VFS node and its
 * `content_hash`) arrives on an edge complete and useless if the bytes stay behind.
 *
 * **This is not a feed gap — it is a separate channel, and deliberately so.** Bytes do
 * not belong in the feed: a delta is `(table, row_id, op)` with no payload precisely so
 * an edge far behind hydrates each row ONCE at its end state. Streaming megabytes through
 * that log would undo the property. So rows sync by delta, bytes sync by reference.
 *
 * **Content addressing is what makes this simple, and it is doing three jobs at once:**
 *  1. **Identity** — the hash IS the name, so no id-mapping between controller and edge.
 *  2. **Idempotency** — re-fetching a hash the edge already has is a no-op; the channel
 *     needs no cursor, no ack, no ordering. It is pure convergence: ask what's missing,
 *     fetch it, store it. A half-finished run simply resumes.
 *  3. **Integrity, for free** — {@see storeBlob()} recomputes the hash from the bytes it
 *     received and REJECTS a mismatch. Corrupted or tampered bytes cannot be stored under
 *     a name they don't hash to, so the transport needs no separate checksum and no trust
 *     in the wire beyond what the address already proves.
 *
 * Mirrors {@see \CoolMS\Core\Backup\BackupContributorInterface}: Core owns neither the
 * bytes nor the meaning, it just collects contributors by tag (`coolms.sync.blob.contributor`)
 * -- so the sync module (L2) can move bytes for VFS (L1) without importing it, and a future module
 * with side-channel bytes joins by implementing this and nothing else.
 */
interface SyncBlobContributorInterface
{
    /** Stable key for logs/reports, e.g. `vfs`. Mirrors `backupKey()`. */
    public function blobKey(): string;

    /**
     * CONTROLLER SIDE — the bytes for `$hash`, or null if this contributor does not have
     * them. Implementations must look wherever their bytes actually live (VFS keeps some
     * in a content-addressed store and materialises others into `public/`), because the
     * caller knows only a hash.
     *
     * Returning null is NOT an error — the requester asks every contributor, and a hash
     * belongs to at most one of them.
     */
    public function readBlob(string $hash): ?string;

    /**
     * EDGE SIDE — hashes this contributor's rows REFER to but whose bytes are absent
     * locally. Exactly the fetch list.
     *
     * Returns at most `$limit` so one pull cannot be unbounded; the channel is
     * convergent, so an over-long list simply finishes on the next pull rather than
     * blocking it. Implementations MUST be cheap enough to call on every pull — this
     * runs even when there is nothing to do.
     *
     * @return list<string>
     */
    public function pendingBlobHashes(int $limit): array;

    /**
     * EDGE SIDE — accept bytes fetched for `$hash`.
     *
     * **MUST verify by recomputing the hash and MUST throw on mismatch** — this is the
     * channel's only integrity check, and it is sufficient (see the class docblock).
     * Never store bytes under a hash they do not produce.
     *
     * Returns false if the bytes were valid but this contributor had nothing to do with
     * them (nothing references the hash any more — the row was deleted mid-pull), which
     * is benign; the next pull simply won't ask again.
     */
    public function storeBlob(string $hash, string $bytes): bool;
}
