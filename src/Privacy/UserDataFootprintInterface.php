<?php

declare(strict_types=1);

namespace CoolMS\Core\Privacy;

/**
 * The marker that gets a footprint declaration collected. A module declares,
 * in code, what it does with a person's data when the account is deleted, so
 * the platform can print its own table and the inventory that measured "full
 * deletion for everyone" is a command rather than a document.
 *
 * The declaration itself is the {@see UserDataFootprint} attribute on the
 * implementing class; this marker is what tags the class ({@see TAG}, through
 * `registerForAutoconfiguration` in the reader's bundle). A class that carries
 * the marker without the attribute makes the reader fail by name: a
 * declaration is never silent.
 *
 * Every handler of an account deletion implements this; a category that keeps
 * data without a handler declares through a class of its own. The holds
 * register -- the operator's per-category obligation and duration -- is the
 * reader's settings block and is named by the reader, not here.
 */
interface UserDataFootprintInterface
{
    public const string TAG = 'coolms.privacy.user_data_footprint';
}
