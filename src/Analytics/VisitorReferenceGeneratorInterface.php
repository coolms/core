<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use DateTimeImmutable;

/**
 * Mints the anonymous `visitorRef` carried on every {@see AnalyticsEvent}
 * under the "resolve identity at the edge" rule. The raw IP + user-agent are
 * resolved to a ref AT THE EDGE and then dropped — the ref is the ONLY visitor
 * handle that ever reaches the event store.
 *
 * Implementations MUST be:
 *  - **non-reversible** (the raw IP/UA cannot be recovered from the ref), and
 *  - **rotating** (the same visitor maps to a DIFFERENT ref across rotation
 *    windows), so correlation is best-effort within a window — the privacy
 *    trade that keeps the analytics store free of durable identity.
 *
 * Lives in Core (L0) so producers at any module level can mint a ref.
 */
interface VisitorReferenceGeneratorInterface
{
    /**
     * @param string $ipAddress the request IP (resolved + dropped here; never stored)
     * @param string $userAgent the request user-agent (resolved + dropped here; never stored)
     *
     * @return string an opaque, fixed-length, rotating reference
     */
    public function forVisitor(string $ipAddress, string $userAgent, DateTimeImmutable $at): string;
}
