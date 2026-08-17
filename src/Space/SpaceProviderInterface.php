<?php

declare(strict_types=1);

namespace CoolMS\Core\Space;

use CoolMS\Core\Identity\UserInterface;

/**
 * Tagged service contract for contributing Library spaces.
 *
 * Each Library tags its providers under its own namespace
 * (e.g. `coolms.media.space_provider`, `coolms.document.space_provider`)
 * and aggregates them via a per-module subclass of
 * {@see \CoolMS\CoreModule\Space\SpaceRegistry}.
 *
 * Providers MAY return an empty list when no spaces apply for the
 * given user. Providers MUST NOT throw on permission denials --
 * they should silently skip the affected entry.
 */
interface SpaceProviderInterface
{
    /**
     * Spaces visible to `$user`, in no particular order.
     * The registry handles sorting + de-duplication.
     *
     * @return list<SpaceInterface>
     */
    public function spacesFor(UserInterface $user): array;
}
