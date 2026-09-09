<?php

declare(strict_types=1);
namespace CoolMS\Core\Navi;

/**
 * Provides default navigation node definitions for the initial site setup.
 *
 * Implementations are tagged 'coolms.default_navi_provider'.
 */
interface DefaultNaviProviderInterface
{
    /**
     * Returns NaviNode definitions to create during default setup.
     *
     * @return list<array{path: string, slug: string, title: string, template: ?string, isVisible: bool, sortOrder: int}>
     */
    public function getDefaultNodes(): array;
}
