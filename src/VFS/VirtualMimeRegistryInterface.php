<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Lookup surface for virtual-mime classification, populated at compile time
 * from every `VirtualMimeProviderInterface`-tagged service.
 *
 * Consumed by `PublicFileManager` to short-circuit storage routing for
 * Nodes whose mime is declared virtual -- those Nodes never live in
 * `document_root` and must always route through secure storage.
 */
interface VirtualMimeRegistryInterface
{
    /**
     * True when any registered provider claims the given mime as virtual.
     * Null / empty mime returns false (cannot be classified).
     */
    public function isVirtualMime(?string $mimeType): bool;
}
