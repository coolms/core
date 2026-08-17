<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Base class for Tier-3 Infrastructure Events.
 *
 * Characteristics:
 * - FAT: $subject may carry any object (entity, Request, Command, DTO)
 * - ALWAYS Sync, ALWAYS in-process (Symfony EventDispatcher)
 * - NEVER cross service boundaries
 * - NEVER dispatched via Symfony Messenger
 *
 * Naming convention: present/past tense describing technical action
 *   PreSaveEvent, PostSaveEvent, OnCreateEvent, OnDeleteEvent
 */
abstract class AbstractLifecycleEvent
{
    public private(set) ?DateTimeInterface $occurredAt = null;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly mixed $subject,
        public readonly array $context = [],
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }
}
