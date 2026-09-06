<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Contributes a map of file-extension -> mime-type pairs that the
 * implementing module owns. The VFS mime detector consults the merged
 * map before falling back to Symfony's standard extension table or
 * raw-bytes sniffing.
 *
 * Implementations are collected by `MimeExtensionRegistryInterface` via
 * the `coolms.vfs.mime_extension_provider` service tag (autoconfigured
 * on this interface, so a concrete provider does not need an explicit
 * tag).
 *
 * Background: Symfony's `MimeTypes` ships a fixed extension table that
 * is unaware of platform-specific extensions like `.dtmpl`. Without a
 * registered provider, `VFSManager::detectMimeType` falls through to
 * finfo's content sniff and ends up with `text/plain` -- which breaks
 * downstream mime filters (page variant pickers, SSR routing, etc.).
 * A registered provider tells VFS which mime to stamp on which
 * extension at file-create / first-write time.
 */
interface MimeExtensionProviderInterface
{
    /**
     * Extension -> mime map contributed by the module. Keys are lowercase
     * extensions WITHOUT a leading dot (e.g., `dtmpl`, not `.dtmpl`);
     * values are mime strings stamped onto `NodeInterface::$mimeType`.
     *
     * @return array<string, string>
     */
    public function getExtensionMimeMap(): array;
}
