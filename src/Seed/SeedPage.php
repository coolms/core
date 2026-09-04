<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

/**
 * One page a seeder intends to create: where it goes, what it is, and its body.
 *
 * ⚠️ The BODY is whatever the platform stores a page as -- not a theme-specific
 * format, and not Markdown. Markdown is an import format, the same decision the
 * docs pipeline already made: it is a seed, and the platform is authoritative
 * afterwards. A theme that ships its bodies in a format the editor cannot open
 * has created content nobody can edit, which is the thing seeding exists to
 * avoid.
 *
 * ⚠️ Note what is NOT identity here. The slug and title are ordinary metadata;
 * idempotency is keyed on the seed marker {@see SeedGuard} writes, so renaming a
 * page does not make the next run create a duplicate.
 */
final readonly class SeedPage
{
    /**
     * @param string               $path   where the page lives, e.g. `/content/default/about`
     * @param string               $locale the body's locale
     * @param string               $body   the platform's own stored representation
     * @param array<string, mixed> $extras title, order, published state, navigation hints --
     *                                     merged into the artefact's extras alongside the marker
     */
    public function __construct(
        public string $path,
        public string $locale,
        public string $body,
        public array $extras = [],
    ) {
    }
}
