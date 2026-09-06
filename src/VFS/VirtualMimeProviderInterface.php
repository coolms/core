<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Contributes a list of mime types that the implementing module owns as
 * "virtual" -- content-addressed Nodes that never live in `document_root`,
 * regardless of their permission bits.
 *
 * Implementations are collected by `VirtualMimeRegistryInterface` via the
 * `coolms.vfs.virtual_mime_provider` service tag (autoconfigured on this
 * interface, so a concrete provider does not need an explicit tag).
 *
 * Background: storage routing in `PublicFileManager::isPubliclyAccessible`
 * historically inferred "lives in document_root" from the o+r / o+x bit walk
 * alone. Virtual Nodes (e.g., Content variants, page Packages) carry those
 * bits for SSR / direct-link reasons but have no on-disk representation. A
 * registered provider tells VFS to treat the mime as virtual so the routing
 * predicate short-circuits to secure-storage.
 */
interface VirtualMimeProviderInterface
{
    /**
     * Mime types the module declares as virtual. Returned values are
     * compared with strict string equality against `NodeInterface::$mimeType`.
     *
     * @return list<string>
     */
    public function getVirtualMimes(): array;
}
