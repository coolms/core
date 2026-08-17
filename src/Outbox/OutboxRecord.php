<?php

declare(strict_types=1);

namespace CoolMS\Core\Outbox;

use CoolMS\Core\Attribute\ClassMeta;
use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use CoolMS\Core\Model\AggregateRootInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * The persisted form of an {@see OutboxMessage} — one row in the transactional
 * outbox. Written in the producer's transaction; a Messenger relay
 * (a later F7 slice) publishes committed rows and stamps `publishedAt`.
 *
 * `publishedAt IS NULL` marks an undelivered row (the relay's poll predicate,
 * indexed); `attempts` counts relay tries for backoff/poison handling; `messageId`
 * carries the producer's stable id for the consumer's idempotency check.
 */
#[ClassMeta(label: 'Outbox message')]
class OutboxRecord implements AggregateRootInterface, IdentifierProviderInterface
{
    use IdentifierProviderTrait {
        IdentifierProviderTrait::__construct as private __identityConstruct;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $type,
        public array $payload,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $createdAt,
        public ?string $messageId = null,
        public ?DateTimeImmutable $publishedAt = null,
        public int $attempts = 0,
    ) {
        $this->__identityConstruct(Uuid::v7());
    }

    public static function fromMessage(OutboxMessage $message, DateTimeImmutable $now): self
    {
        return new self(
            type: $message->type,
            payload: $message->payload,
            occurredAt: $message->occurredAt ?? $now,
            createdAt: $now,
            messageId: $message->messageId,
        );
    }
}
