<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

/**
 * A generic "please notify somebody" integration event.
 *
 * The decoupling seam between any module that has something worth telling a person
 * and the Notification module. The *requesting* module dispatches this with its own
 * opaque type string and its own copy -- it never imports the notifier. The
 * Notification module's generic listener fulfils it, knowing nothing about the
 * requester.
 *
 * Living in Core (L0) keeps the dependency arrows legal in both directions:
 * requesters (L2+) and the notifier each depend only downward on Core. Exactly the
 * shape {@see StartWorkflowRequested} already uses for the workflow engine, and for
 * the same reason -- a direct call from one module into another's service is a port
 * call across a boundary that may one day be a network.
 *
 * `$type` is an opaque, dot-namespaced string owned by the requester
 * (`email.mailbox.connection_failed`, `document.generation.completed`). It is NOT
 * the manual-send enum, which gates only the admin's send dialog.
 *
 * Dispatched synchronously through the Symfony event dispatcher, like
 * {@see StartWorkflowRequested}: the notification exists by the time the requesting
 * action returns. A module that needs delivery to survive its own process failure
 * should use the outbox instead.
 */
final readonly class NotificationRequested
{
    /**
     * @param string               $userId   the recipient's UUID (RFC 4122 form)
     * @param string               $type     opaque, dot-namespaced, owned by the requester
     * @param string               $title    short summary line
     * @param string               $body     the message
     * @param array<string, mixed> $metadata requester-owned payload for the client (ids, deep-link keys)
     */
    public function __construct(
        public string $userId,
        public string $type,
        public string $title,
        public string $body,
        public array $metadata = [],
    ) {
    }
}
