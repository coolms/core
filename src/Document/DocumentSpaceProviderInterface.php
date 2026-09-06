<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

use CoolMS\Core\Space\SpaceProviderInterface;

/**
 * Document-flavoured marker for {@see SpaceProviderInterface} implementations.
 *
 * Carries no contract of its own -- it exists purely as the
 * autoconfigure target so the Document DI extension can tag implementors
 * `coolms.document.space_provider` without sweeping every generic
 * `SpaceProviderInterface` in the container (which would also catch
 * Media providers and double-tag).
 *
 * Implementations are tagged `coolms.document.space_provider` and
 * collected by `DocumentSpaceRegistry` in the consuming application.
 *
 * Providers MAY return an empty list when no spaces apply for the
 * given user. Providers MUST NOT throw on permission denials --
 * they should silently skip the affected entry.
 */
interface DocumentSpaceProviderInterface extends SpaceProviderInterface
{
}
