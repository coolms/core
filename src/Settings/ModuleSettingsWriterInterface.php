<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * Write one module settings block, optionally for one site.
 *
 * The narrow counterpart to {@see ModuleSettingsReaderInterface}. It exists
 * because callers that only need to set a value should not have to depend on the
 * whole settings manager -- its definitions, its reset, its storage ids -- and
 * because a concrete final class cannot be substituted in a test, which made the
 * one thing worth testing about a write (that it happens, and in what order)
 * unreachable.
 *
 * !! `$scope` is the site's identifier, and passing one to a block that did not
 * declare itself `siteScopable` is a programming error the implementation
 * refuses -- unlike reading, which forgives it and answers platform-wide.
 * Writing cannot afford that: a scoped value silently stored against the
 * platform would apply to every site.
 */
interface ModuleSettingsWriterInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @return string where it was stored
     */
    public function write(string $key, array $data, ?string $scope = null): string;
}
