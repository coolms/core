<?php

declare(strict_types=1);

namespace CoolMS\Core\Mail;

/**
 * Turns an author-written rich body into something a mail client renders.
 *
 * **Why this exists at all.** Before it, a campaign sender would send its
 * body verbatim — `->html($body . $hardcodedFooter)`. That is fine for hand-typed
 * HTML and quietly wrong for anything the rich editor produces: the editor's
 * media insert emits a dtmpl `{widget:media:UUID …}` tag, nothing on the send
 * path rendered dtmpl, and so a subscriber would have received that tag as
 * literal text. Rich composition and "special processing before send" are the
 * same problem, so they live in one place.
 *
 * The pipeline, in order — each step is why a later one can work:
 *
 *   1. **Render dtmpl.** Widget tags become real markup, so `{widget:media:…}`
 *      turns into an `<img src="/media/…">`. Body variables from the message
 *      context resolve here too.
 *   2. **Wrap in the theme's email layout.** `templates/emails/{template}.html.dtmpl`
 *      from the active theme, platform default when the theme ships none. This is
 *      what lets a theme restyle every outbound mail — header, footer, colours,
 *      the unsubscribe line — without a single module knowing it happened.
 *   3. **Inline local images.** Every `<img src>` pointing at this install is read
 *      and re-attached as a `cid:` part. Mail clients block remote images by
 *      default, so an emailed newsletter that merely links its pictures arrives
 *      blank for most recipients; a CID part renders immediately. External URLs
 *      are left exactly as they are — rewriting someone else's CDN link would be
 *      both wrong and a fetch we have no right to make.
 *   4. **Derive plain text.** The `text/plain` alternative, from the rendered HTML
 *      rather than the raw body, so it never leaks widget syntax.
 *
 * **Composition never sends.** That split is what makes a preview ("show me this
 * campaign as a subscriber sees it") and a unit test possible without a mailer —
 * see {@see RichMailSenderInterface} for the sending half.
 *
 * **Implemented at L3** (the Mail module), because rendering needs the theme and
 * the VFS. The contract sits here at L0 so an L2 module can
 * depend on it without importing upward.
 */
interface RichMailComposerInterface
{
    /**
     * @throws Exception\MailCompositionException when the
     *                                            body or the theme layout cannot be rendered. Attachment and image
     *                                            problems do NOT throw: a missing or unreadable image leaves its
     *                                            `<img>` untouched rather than failing the whole send, because one
     *                                            broken picture is not a reason to drop a campaign.
     */
    public function compose(RichMailMessage $message): ComposedMail;
}
