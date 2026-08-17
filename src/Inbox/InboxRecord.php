<?php

declare(strict_types=1);

namespace CoolMS\Core\Inbox;

use CoolMS\Core\Attribute\ClassMeta;
use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use CoolMS\Core\Model\AggregateRootInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * The consumer-side dedupe record — one row per
 * `(consumer, messageId)` a handler has already processed. The partner to the
 * outbox: at-least-once delivery is safe to process twice because a replay hits
 * the UNIQUE `(consumer, message_id)` constraint and the handler skips.
 *
 * Mapping-only: the table is written and read by the persistence adapter's
 * store (an atomic `INSERT ... ON CONFLICT DO NOTHING`); this entity exists so
 * `schema:create`/`schema:validate` know the table. The `consumer` key lets two
 * distinct handlers each process the same message id once.
 */
#[ClassMeta(label: 'Processed inbox message')]
class InboxRecord implements AggregateRootInterface, IdentifierProviderInterface
{
    use IdentifierProviderTrait {
        IdentifierProviderTrait::__construct as private __identityConstruct;
    }

    public function __construct(
        public string $consumer,
        public string $messageId,
        public DateTimeImmutable $processedAt,
        /**
         * Opaque id of the aggregate that claimed this key.
         *
         * NULL for plain {@see ProcessedMessageStoreInterface::firstSeen} rows,
         * which only need "seen or not". Set by `firstSeenRef` so an inbound
         * re-delivery can be mapped back onto the SAME Lead instead of merely
         * skipped. Kept as a bare string, not a typed relation: this is Core L0
         * and must not know what kind of aggregate claimed it.
         */
        public ?string $ref = null,
    ) {
        $this->__identityConstruct(Uuid::v7());
    }
}
