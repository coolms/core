<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * The request-edge seam that resolves the
 * CURRENT request's anonymous `visitorRef` — the daily-rotating, non-reversible
 * handle (see {@see VisitorReferenceGeneratorInterface}) producers stamp on an
 * {@see AnalyticsEvent} so a conversion stitches to the visitor's pageview
 * journey without any durable identity ever reaching the store.
 *
 * Reading the raw IP + user-agent off the request and dropping them happens
 * BEHIND this port (derive-and-drop), so producers never touch PII. Returns
 * **null off the request edge** — CLI, a Messenger worker, or a request with no
 * resolvable client IP — so producers degrade to a null ref rather than failing.
 *
 * Lives in Core (L0) so producers at any module level can stamp a ref.
 */
interface CurrentVisitorReferenceInterface
{
    public function current(): ?string;
}
