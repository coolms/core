<?php

declare(strict_types=1);

namespace CoolMS\Core\Identity;

use DateTimeInterface;

/**
 * Where the log-only elevation gates write what they saw, and where the
 * confirmation report reads it back before the narrowing is enforced.
 *
 * Two series. COUNTS, per gate and day: how many times the gate's decision
 * point was reached, how many times membership bypassed where elevation would
 * have refused, and -- on the `*` row -- how many requests were served, so that
 * zero hits beside zero evaluations reads as "no traffic" and never as
 * "clean". HITS, distinct per (user, gate, permission, path): what the refusals
 * were about, which is what the shape-by-subtree and per-user criteria are
 * computed from.
 *
 * A Core port because the writer (Identity's recorder) and the reader (the VFS
 * module's report, which owns the permission model the baseline was counted
 * against) are different modules, and neither may import the other.
 */
interface ElevationShadowStoreInterface
{
    public function addCounts(string $day, string $gate, int $evaluations, int $hits, int $requests): void;

    public function addHit(string $userId, string $gate, string $permission, string $path, int $hits, DateTimeInterface $seenAt): void;

    /**
     * Summed over days from `$sinceDay` (inclusive, `YYYY-MM-DD`) or over everything.
     *
     * @return list<array{gate: string, evaluations: int, hits: int, requests: int}>
     */
    public function counts(?string $sinceDay = null): array;

    /** @return list<array{user_id: string, gate: string, permission: string, path: string, hits: int}> */
    public function hits(): array;
}
