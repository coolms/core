<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

/**
 * The transport-ready result of {@see RichMailComposerInterface::compose()}:
 * final HTML, a plain-text alternative, and every part that must ride along.
 *
 * Deliberately NOT a `Symfony\Component\Mime\Email`. This VO lives at L0 where
 * every module can name it, and keeping it plain data means the composer stays
 * unit-testable without a mailer and a caller can preview a message ("send me a
 * test") without building or sending anything.
 */
final readonly class ComposedMail
{
    /**
     * @param list<string>             $to
     * @param list<RichMailAttachment> $inline      parts referenced from $html as `cid:…`
     * @param list<RichMailAttachment> $attachments parts shown in the client's attachment tray
     * @param array<string, string>    $headers
     */
    public function __construct(
        public array $to,
        public string $subject,
        public string $html,
        public string $text,
        public array $inline = [],
        public array $attachments = [],
        public array $headers = [],
        public ?string $from = null,
    ) {
    }
}
