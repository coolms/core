<?php

declare(strict_types=1);

namespace CoolMS\Core\Api;

/**
 * Provides the API resource definitions a module wants registered in the VFS.
 *
 * Tag: coolms.api.resource.installer (applied via Core Extension autoconfiguration).
 */
interface ApiResourceInstallerInterface
{
    /** @return ApiResourceDefinition[] */
    public function getResources(): array;
}
