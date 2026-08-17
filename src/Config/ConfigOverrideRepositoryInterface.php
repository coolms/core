<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

/**
 * The port the config store persists through.
 *
 * Domain-side so the writer and the loader depend on an interface rather than
 * on the ORM — the platform's ORM-agnostic rule, and the reason a `(type, id)`
 * override could later live somewhere that is not a relational table without
 * anything above this changing.
 */
interface ConfigOverrideRepositoryInterface
{
    public function findOverride(string $type, string $id): ?ConfigOverride;

    /**
     * @param array<string, mixed> $data
     */
    public function upsert(string $type, string $id, array $data): ConfigOverride;

    /** True when a row was removed; false when there was none to remove. */
    public function deleteOverride(string $type, string $id): bool;
}
