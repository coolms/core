<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

use CoolMS\Core\Mail\Exception\InvalidRichMailException;

use function trim;

/**
 * What a module wants to send: a subject, an author-written rich body, and the
 * context needed to dress it up. The transport-shaped result is
 * {@see ComposedMail}.
 *
 * **The body is authored markup, not final HTML.** It may still contain dtmpl
 * widget tags (`{widget:media:UUID …}`) and `<img src="/media/…">` references,
 * because that is what the rich editor produces. Turning it into something a
 * mail client renders is exactly the composer's job — see
 * {@see RichMailComposerInterface}. Callers must NOT pre-render it, or inline
 * images silently stop working.
 *
 * **`template` names a theme layout, not a file.** The composer resolves it
 * against the active theme (`templates/emails/{template}.html.dtmpl`) with a
 * platform default as the fallback, so a theme can restyle every outbound mail
 * without any module knowing. `null` means "no wrapper" — the body is the whole
 * email, which is what a plain notification wants.
 *
 * **`context` is the layout's variables**, not the body's: a site name, a
 * greeting, an unsubscribe URL. Keeping it separate from the body is what lets
 * the unsubscribe link be a *link in the template* instead of a string
 * concatenated onto the HTML — the shape campaign senders used before this
 * seam existed, which made the footer unstylable and English-only.
 */
final readonly class RichMailMessage
{
    /**
     * @param list<string>             $to
     * @param array<string, mixed>     $context     variables for the theme layout
     * @param list<RichMailAttachment> $attachments
     * @param array<string, string>    $headers     extra headers the caller owns (e.g. RFC 8058 List-Unsubscribe)
     */
    public function __construct(
        public array $to,
        public string $subject,
        public string $body,
        public ?string $template = null,
        public array $context = [],
        public array $attachments = [],
        public array $headers = [],
        /** Overrides the module's configured default sender when set. */
        public ?string $from = null,
        /**
         * The user whose read permissions gate every VFS read during composition
         * — inline images and path attachments alike.
         *
         * `null` means a system-originated mail, which composes as **anonymous**:
         * the VFS resolves it against the `other` permission bits, so a public
         * asset still embeds and a private one still does not. It is never a
         * system or root identity.
         */
        public ?string $senderUserId = null,
    ) {
        if ('' === trim($subject)) {
            throw InvalidRichMailException::emptySubject();
        }
        if ([] === $to) {
            throw InvalidRichMailException::emptyRecipient();
        }
    }

    /**
     * Convenience for the overwhelmingly common single-recipient case.
     *
     * @param array<string, mixed> $context
     */
    public static function toOne(
        string $recipient,
        string $subject,
        string $body,
        ?string $template = null,
        array $context = [],
    ): self {
        return new self([$recipient], $subject, $body, $template, $context);
    }

    /** @param array<string, string> $headers */
    public function withHeaders(array $headers): self
    {
        return new self(
            $this->to,
            $this->subject,
            $this->body,
            $this->template,
            $this->context,
            $this->attachments,
            $headers + $this->headers,
            $this->from,
            $this->senderUserId,
        );
    }

    /** @param list<RichMailAttachment> $attachments */
    public function withAttachments(array $attachments): self
    {
        return new self(
            $this->to,
            $this->subject,
            $this->body,
            $this->template,
            $this->context,
            $attachments,
            $this->headers,
            $this->from,
            $this->senderUserId,
        );
    }
}
