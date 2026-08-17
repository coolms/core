<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

/**
 * Marks an exception caused by bad input rather than a broken server.
 *
 * `UnhandledExceptionListener` renders these as HTTP 400. Any module can opt an
 * exception in by implementing this; Core does not have to know the module, which
 * is the point -- it previously named VFS's `InvalidPathException` directly.
 *
 * Only for exceptions that are ALWAYS the caller's fault. An exception that is a
 * bad request in one path and a server fault in another belongs to neither, and
 * the honest answer there is to throw two different types.
 */
interface InvalidInputExceptionInterface
{
}
