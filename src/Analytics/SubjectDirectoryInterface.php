<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

use Symfony\Component\Uid\Uuid;

/**
 * C.7 — published read-port: resolve a platform `userId` to its CDP
 * {@see SubjectSummary}, or null when no such subject exists yet.
 *
 * The write-side sibling of the analytics sink port ({@see AnalyticsEventSinkInterface}),
 * on the read side. It lives in Core (L0) — NOT in Analytics — on purpose:
 * Analytics is a HIGHER-level module (L2) than its Contact consumer (L1), so an
 * Analytics-Domain port would be a level inversion. Hosting the contract at L0
 * (implemented by an Analytics Infrastructure adapter) mirrors the shipped
 * event-sink seam and keeps Contact importing only Core.
 *
 * The CDP `Subject` is keyed `known:<userId>` for a known subject, so this
 * resolves off the userId alone; it returns only counts-only signal (no PII) and
 * is best-effort — a subject exists only once the CDP build job has run over the
 * event stream. "Cross-link, never merge": this is a read handle,
 * not a shared identity.
 */
interface SubjectDirectoryInterface
{
    /**
     * The CDP subject summary for this user, or null when none exists (the
     * common case until the CDP build job has produced a `known:<userId>` row).
     */
    public function summaryForUser(Uuid $userId): ?SubjectSummary;

    /**
     * Batch variant — resolve many userIds at once. Only RESOLVED users appear
     * in the result (a userId with no subject is simply absent), so a list view
     * avoids N round-trips. Mirrors the identity module's user-directory `displayNames()`.
     *
     * @param list<Uuid> $userIds
     *
     * @return array<string, SubjectSummary> RFC-4122 userId => summary
     */
    public function summariesForUsers(array $userIds): array;
}
