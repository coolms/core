<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * Request-edge seam (Track E Phase 4 — content personalization) that resolves
 * the CDP segment keys the CURRENT request's subject belongs to.
 *
 * The fourth "Current*" request-edge reader alongside
 * {@see CurrentVisitorReferenceInterface}, {@see CurrentRequestDimensionsInterface}
 * and {@see CurrentConsentInterface}: it maps this request (its logged-in user,
 * else its anonymous daily-rotating visitorRef) to the visitor's own
 * low-cardinality segment labels, so a cache-safe client can personalize content
 * WITHOUT the server varying the byte-identical SSR response — the
 * Experiment-module pattern (client-side assignment over a cacheable page); see
 * `docs/design/analytics-cdp-personalization.md`.
 *
 * Personalization is a consent-gated purpose. Returns **`[]`** when the visitor
 * has not granted the required consent, when there is no request edge (CLI /
 * worker), or when no CDP subject exists for
 * the visitor yet — so a consumer degrades to the default (unpersonalized)
 * content instead of failing.
 *
 * Lives in Core (L0) so the Web SSR layer and any consumer can read segments
 * without depending on Analytics internals; Analytics implements it, mirroring
 * {@see SubjectDirectoryInterface} (contract in Core, adapter in Analytics — on
 * a service split it swaps to an HTTP/replicated read with no consumer change).
 */
interface CurrentSubjectSegmentsInterface
{
    /** @return list<string> the current subject's segment keys, or [] */
    public function current(): array;
}
