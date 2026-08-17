<?php

declare(strict_types=1);

namespace CoolMS\Core\Option;

/**
 * Tagged service contract for advertising a list of {@see Option}s
 * to the rest of the platform.
 *
 * Module authors implement this to expose a select datasource —
 * timezones, message handlers, user lookups, calendar slugs, role
 * names, anything where the FE wants a friendly picker instead of a
 * free-text input. The {@see \CoolMS\CoreModule\Option\OptionSourceRegistry}
 * collects every tagged implementation and routes API requests by key.
 *
 * Each implementation is autoconfigured with the tag
 * `coolms.option.source` — see {@see \CoolMS\CoreBundle\DependencyInjection\Extension::load}.
 *
 * **Keying convention.** `$key` is dot-namespaced lowercase
 * (`<module>.<noun>`), e.g. `calendar.timezones`, `identity.users`,
 * `scheduler.handlers`. Two providers with the same key throw at boot
 * via {@see \CoolMS\CoreModule\Option\OptionSourceRegistry}.
 *
 * **Provide semantics.** Implementations decide their own ordering
 * (the registry doesn't re-sort). When `$query` is supplied, it's a
 * case-insensitive substring narrowing on `value` + `label`. When
 * `$limit` is supplied it caps the returned list (the caller honours
 * the cap; the provider doesn't have to clip — the registry does that
 * defensively, but providers SHOULD clip themselves for efficiency).
 */
interface OptionSourceProviderInterface
{
    public function key(): string;

    /**
     * @return list<Option>
     */
    public function provide(?string $query = null, ?int $limit = null): array;
}
