<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * Where one direct send stands, in the same words for every channel. The channel's own status and code are kept beside
 * it for audit; this is what a sender decides on.
 *
 * Progress only moves forward: queued, accepted, delivered, read. `failed` and `expired` end an attempt that has not
 * been delivered; once delivered, a later failure is recorded and not applied. `unknown` is the provider saying it
 * cannot tell: it moves nothing.
 */
enum DeliveryStatus: string
{
    case Queued = 'queued';
    case Accepted = 'accepted';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';
    case Expired = 'expired';
    case Unknown = 'unknown';

    /** The step on the way to the person: 0 queued ... 3 read; null for an outcome that is not progress. */
    public function progress(): ?int
    {
        return match ($this) {
            self::Queued => 0,
            self::Accepted => 1,
            self::Delivered => 2,
            self::Read => 3,
            self::Failed, self::Expired, self::Unknown => null,
        };
    }

    /** Whether the attempt ended without reaching the person. */
    public function isTerminalFailure(): bool
    {
        return self::Failed === $this || self::Expired === $this;
    }

    /** Whether this status is at least what a sender waits for. */
    public function reaches(self $waitFor): bool
    {
        $have = $this->progress();
        $want = $waitFor->progress();

        return null !== $have && null !== $want && $have >= $want;
    }

    /**
     * The status an attempt has after this one is reported to it: progress only forward, a failure only before
     * delivery, unknown never.
     */
    public function then(self $reported): self
    {
        if (self::Unknown === $reported || $this->isTerminalFailure()) {
            return $this;
        }
        if ($reported->isTerminalFailure()) {
            return ($this->progress() ?? 0) >= 2 ? $this : $reported;
        }

        return ($reported->progress() ?? 0) > ($this->progress() ?? 0) ? $reported : $this;
    }
}
