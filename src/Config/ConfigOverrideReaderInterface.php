<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

/**
 * "Has anything overridden this config?" -- the platform's whole interest in
 * stored config data.
 *
 * ## Why the platform only READS here
 *
 * `config/modules/**` is the editable config layer, and a deployment that
 * ships a read-only image cannot use it -- so an admin save has to land
 * somewhere else, and the next request has to find it there. Reading that
 * "somewhere else" is platform work: the config chain is consulted on every
 * request, long before any module has had an opinion.
 *
 * WRITING it is not. A stored override is what one deployment's operator
 * decided, which makes it operator configuration, which makes it the Settings
 * module's -- along with the table it lives in. The platform therefore declares
 * this port and reads through it; a host with no module answering gets the
 * files, which is what every host got before the table existed.
 *
 * !! Implementations must not let a MISSING store take a request down. Config
 * is read on databases that have not been migrated yet -- a fresh checkout, a
 * CI job that builds before it migrates, an installer running its own first
 * migration -- and "no store" has to read as "no override" rather than as an
 * error. From here a store that cannot answer and a config nobody has
 * overridden are the same thing: null, then the files.
 */
interface ConfigOverrideReaderInterface
{
    /**
     * The stored config array for `(type, id)`, or null when nothing is stored.
     *
     * The array is the WHOLE config, `type` and `id` keys included, exactly as
     * a YAML file would have held it -- a stored answer is indistinguishable
     * from the file it stands in for, so no consumer needs a second shape.
     *
     * @return array<string, mixed>|null
     */
    public function findOverrideData(string $type, string $id): ?array;
}
