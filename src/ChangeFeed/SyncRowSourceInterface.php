<?php

declare(strict_types=1);

namespace CoolMS\Core\ChangeFeed;

/**
 * Where {@see \CoolMS\CoreModule\ChangeFeed\SyncChangeApplier} fetches the CURRENT data
 * of the rows an `upsert` delta names. The lean change feed carries only
 * `(table, row-UUID, op)`, so applying an upsert needs the row's live data from the
 * authoritative side.
 *
 * A per-call collaborator, NOT an autowired singleton: the source is `local` when a host
 * replays its own feed (over {@see \CoolMS\Core\Backup\TableBackupPortInterface::dumpRowsByIds},
 * the persistence adapter supplies that one) and `remote`
 * when an EDGE hydrates from its controller over HTTP (`/api/v1/sync/rows`, the edge-pull
 * command, B.2.4c). The applier takes the source as a method argument so neither impl is
 * baked into the container.
 */
interface SyncRowSourceInterface
{
    /**
     * The current rows of `$table` for `$ids` (order unspecified; ids matching no live row
     * are simply absent), in the same shape backup exports — ready for `restoreRows`.
     *
     * @param list<string> $ids
     *
     * @return list<array<string, mixed>>
     */
    public function fetchRows(string $table, array $ids): array;
}
