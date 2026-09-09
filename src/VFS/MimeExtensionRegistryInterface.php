<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Lookup surface for module-contributed extension-to-mime mappings,
 * populated at compile time from every
 * `MimeExtensionProviderInterface`-tagged service.
 *
 * Consumed by `VFSManager::detectMimeType` ahead of Symfony's
 * `MimeTypes` table so platform-specific extensions (e.g., `.dtmpl`)
 * resolve to the mime their owning module declares, rather than
 * falling through to a finfo-sniffed `text/plain`.
 */
interface MimeExtensionRegistryInterface
{
    /**
     * Mime registered for the given extension, or null when no provider
     * claims it. The extension is normalized to lowercase and stripped
     * of any leading dot before lookup.
     */
    public function mimeForExtension(string $ext): ?string;
}
