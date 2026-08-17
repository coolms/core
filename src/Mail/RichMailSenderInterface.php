<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

/**
 * Composes a {@see RichMailMessage} and hands it to the mailer — the one call a
 * module makes when it just wants the mail to go out.
 *
 * Separate from {@see RichMailComposerInterface} so that composing and sending
 * can be exercised independently: a preview endpoint composes and never sends,
 * and a test asserts on {@see ComposedMail} without a transport. Callers that
 * only want delivery should depend on this and stay unaware of MIME entirely.
 *
 * **Failures propagate.** A transport error is thrown, not swallowed, so a
 * Messenger handler retries the message per its transport's retry strategy
 * instead of silently dropping a recipient. A caller that must not fail its own
 * request (a public signup sending a confirmation, say) is responsible for
 * catching — that decision belongs to the caller, not to this seam.
 */
interface RichMailSenderInterface
{
    /**
     * @throws Exception\MailCompositionException                              when the body
     *                                                                         or theme layout cannot be rendered
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface on a
     *                                                                         delivery failure
     */
    public function send(RichMailMessage $message): void;
}
