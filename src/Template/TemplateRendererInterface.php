<?php

declare(strict_types=1);

namespace CoolMS\Core\Template;

use Throwable;

/**
 * Renders a stored template file to markup.
 *
 * Core's web layer needs HTML rendering without knowing which engine or storage
 * provides it -- `AbstractController` previously imported the dTMPL/VFS handler
 * directly, which put a module dependency in the kernel.
 *
 * Implementations resolve the acting user from the security context themselves;
 * the caller does not pass one.
 */
interface TemplateRendererInterface
{
    /**
     * @param string               $path    storage path of the template, extension included
     * @param array<string, mixed> $context variables exposed to the template
     *
     * @throws Throwable when the template cannot be read or rendered
     */
    public function render(string $path, array $context = []): string;
}
