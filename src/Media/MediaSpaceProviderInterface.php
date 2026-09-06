<?php

declare(strict_types=1);
namespace CoolMS\Core\Media;

use CoolMS\Core\Space\SpaceProviderInterface;

/**
 * Media-flavoured marker for {@see SpaceProviderInterface} implementations.
 *
 * Carries no contract of its own -- it exists purely as the
 * autoconfigure target so the Media DI extension can tag implementors
 * `coolms.media.space_provider` without sweeping every generic
 * `SpaceProviderInterface` in the container (which would also catch
 * Document providers and double-tag).
 *
 * Implementations are tagged `coolms.media.space_provider` and
 * collected by `MediaSpaceRegistry` in the consuming application.
 *
 * Providers MAY return an empty list when no spaces apply for the
 * given user. Providers MUST NOT throw on permission denials --
 * they should silently skip the affected entry.
 */
interface MediaSpaceProviderInterface extends SpaceProviderInterface
{
}
