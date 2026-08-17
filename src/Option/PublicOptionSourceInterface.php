<?php

declare(strict_types=1);

namespace CoolMS\Core\Option;

/**
 * Opt-in marker for an {@see OptionSourceProviderInterface} whose list is
 * safe to serve to ANONYMOUS callers (the public website), not just
 * authenticated admin users.
 *
 * The generic `GET /api/v1/options/{source}` endpoint is gated
 * `IS_AUTHENTICATED_FULLY` — fine for the admin SPA, but a public SSR form
 * (a contact form's country dropdown, say) has no Bearer token and can't
 * reach it. A source additionally implementing THIS interface also becomes
 * reachable at the firewall-public `GET /api/v1/public-options/{source}`
 * ({@see \CoolMS\CoreBundle\ApiPlatform\Resource\OptionResource}),
 * so an `api`-data-source select on a public page can populate itself.
 *
 * **Default is closed.** A plain `OptionSourceProviderInterface` is
 * admin-only; exposure to the public surface is a deliberate, per-source
 * decision made by implementing this marker. Only flag sources whose data
 * is inherently public and non-enumerable-sensitive — static reference
 * lists (ISO countries, IANA timezones), NOT repository-backed lookups
 * that could leak rows (users, calendars, leads).
 *
 * The public endpoint resolves a source by key and serves it ONLY when it
 * is `instanceof` this interface; a non-public key returns 404 (not 403),
 * so the public surface never confirms a private source even exists.
 *
 * Marker only — no methods. Implementing it is the entire contract; the
 * autoconfigure tag (`coolms.option.source`) still applies because this
 * interface extends {@see OptionSourceProviderInterface}.
 */
interface PublicOptionSourceInterface extends OptionSourceProviderInterface
{
}
