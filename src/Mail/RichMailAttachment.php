<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

use CoolMS\Core\Mail\Exception\InvalidRichMailException;

use function ltrim;
use function strrpos;
use function substr;
use function trim;

/**
 * One file travelling with a {@see RichMailMessage} — either a normal
 * attachment or an image inlined into the HTML body.
 *
 * **Two sources, deliberately.** A caller either names a VFS path
 * ({@see fromVfsPath}) and lets the composer read it under the sender's own
 * permissions, or hands over bytes it already holds ({@see fromBytes}, e.g. a
 * freshly rendered PDF that was never written to storage). Keeping both on one
 * VO means the composer has a single attachment loop instead of a branch per
 * caller.
 *
 * **`cid` is what makes an image inline.** A non-null `cid` tells the composer
 * to attach this part with that Content-ID and expect the body's `<img src>` to
 * reference `cid:{cid}` — the form mail clients render without the
 * "load remote images?" prompt. A null `cid` is an ordinary attachment shown in
 * the client's attachment tray. See {@see RichMailComposerInterface} for why
 * remote `src` URLs are rewritten this way rather than left alone.
 */
final readonly class RichMailAttachment
{
    /**
     * @param string      $filename    the name the recipient sees
     * @param string|null $vfsPath     absolute VFS path to read, or null when $bytes is set
     * @param string|null $bytes       raw file content, or null when $vfsPath is set
     * @param string|null $contentType MIME type; the composer sniffs it when null
     * @param string|null $cid         Content-ID for an inline part; null for a plain attachment
     */
    private function __construct(
        public string $filename,
        public ?string $vfsPath,
        public ?string $bytes,
        public ?string $contentType,
        public ?string $cid,
    ) {
        if ('' === trim($filename)) {
            throw InvalidRichMailException::emptyAttachmentFilename();
        }
    }

    /**
     * Attach a file the composer will read from the VFS.
     *
     * The composer reads it as the SENDING user, never as a system identity —
     * a message must not be able to exfiltrate a file its author cannot open.
     */
    public static function fromVfsPath(string $path, ?string $filename = null, ?string $contentType = null): self
    {
        $path = trim($path);
        if ('' === $path) {
            throw InvalidRichMailException::emptyAttachmentPath();
        }

        return new self(
            filename: $filename ?? self::basename($path),
            vfsPath: $path,
            bytes: null,
            contentType: $contentType,
            cid: null,
        );
    }

    /** Attach content the caller already holds in memory. */
    public static function fromBytes(string $filename, string $bytes, ?string $contentType = null): self
    {
        return new self(
            filename: $filename,
            vfsPath: null,
            bytes: $bytes,
            contentType: $contentType,
            cid: null,
        );
    }

    /**
     * Return a copy of this attachment marked inline under `$cid`, so the body
     * can address it as `cid:{$cid}`.
     */
    public function inlinedAs(string $cid): self
    {
        $cid = trim($cid);
        if ('' === $cid) {
            throw InvalidRichMailException::emptyContentId();
        }

        return new self($this->filename, $this->vfsPath, $this->bytes, $this->contentType, $cid);
    }

    /** True when this part is referenced from the body rather than listed as an attachment. */
    public function isInline(): bool
    {
        return null !== $this->cid;
    }

    /**
     * Last path segment. Deliberately not `basename()`: that is locale- and
     * separator-sensitive on some platforms, and VFS paths are always `/`.
     */
    private static function basename(string $path): string
    {
        $trimmed = ltrim($path, '/');
        $cut = strrpos($trimmed, '/');

        return false === $cut ? $trimmed : substr($trimmed, $cut + 1);
    }
}
