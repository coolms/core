<?php

declare(strict_types=1);

namespace CoolMS\Core\Template;

/**
 * Neutral SPI for one piece of context enrichment, plugged into a context
 * builder. Consumers (Web SSR, Document generation, future Email/Notification
 * templates) own their own tag namespace so contributor sets do not
 * cross-pollute -- each builder collects only the tag it is interested in.
 *
 * Implementations:
 *  - MUST be idempotent and stateless across invocations.
 *  - MUST return JSON-serializable arrays (no entities/closures/resources).
 *    A builder's output is persisted on a row in the Document use case, and
 *    travels through a message bus in others.
 *  - SHOULD return `[]` when there is nothing to contribute for the current
 *    base context.
 *
 * The interface itself has no DI tag -- registration is the concrete builder's
 * responsibility, and each names its own tag.
 *
 * !! IT LIVES HERE, IN THE CONTRACTS PACKAGE, BECAUSE THREE INTERFACES IN THIS
 * PACKAGE EXTEND IT. While it sat one tier up, `coolms/core` imported a package
 * that requires `coolms/core` back -- an inversion that could not be fixed by
 * declaring the dependency, because declaring it would have made the cycle
 * explicit rather than removing it. Nothing about the contract needed the
 * higher tier: it names no type at all, its own or anyone else's.
 *
 * {@see \CoolMS\CoreModule\Template\ContextContributorInterface} still exists
 * and extends this, so any consumer typed against the old name keeps working.
 */
interface ContextContributorInterface
{
    /**
     * Contribute fields to the rendering context. The builder deep-merges the
     * return value into the accumulated context; later contributors see
     * earlier contributors' contributions via the `$context` argument.
     *
     * @param array<string, mixed> $context context assembled so far
     *
     * @return array<string, mixed> key/value pairs to merge in
     */
    public function contribute(array $context): array;
}
