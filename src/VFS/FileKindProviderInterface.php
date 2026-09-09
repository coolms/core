<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Implemented by any module that can create files, so the File Manager -- and
 * anywhere else that offers "new file" -- can list them.
 *
 * A template is just a file. Once the Documents module is installed, the VFS
 * file manager should be able to make one, in any folder, without VFS knowing
 * what a template IS. That is the whole point of this seam: the menu is
 * assembled from whoever is installed, so removing a module removes its entries
 * and adding one adds them, with no edit here and none in the client.
 *
 * Auto-tagged `coolms.vfs.file_kind_provider` by the VFS extension. !! Modules
 * that register services with `setAutoconfigured(false)` must add the tag
 * EXPLICITLY -- implementing the interface is not enough there, which has caught
 * a contributor before.
 */
interface FileKindProviderInterface
{
    /**
     * The kinds this module offers, in the order it wants them shown.
     *
     * May be empty -- a module whose creation surface depends on configuration
     * can return nothing rather than offering a choice that would fail.
     *
     * @return list<CreatableFileKind>
     */
    public function fileKinds(): array;
}
