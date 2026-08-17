<?php

declare(strict_types=1);

namespace CoolMS\Core\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

/**
 * Moment-range: a pair of TZ-aware timestamps marking a window in time.
 *
 * Unlike {@see DateRange}, timezone and time-of-day are **preserved**
 * exactly as given. The constructor does not normalise to UTC --
 * callers that want zone-stable persistence should normalise before
 * construction. Comparisons are correct across zones because
 * `DateTimeImmutable` comparators compare instants.
 *
 * **`$includesEnd` default**: `true` (closed range `[start, end]`).
 * For most LCAP/BPM consumers (workflow timer events, Calendar event
 * windows once retrofitted, Scheduler validity windows) the inclusive
 * end is the natural interpretation -- "this window is valid up to and
 * including 17:00".
 *
 * **Storage**: serialised to JSON via {@see self::toArray} as
 * `{"start": "ISO-8601", "end": "ISO-8601", "includesEnd": bool}`.
 * Persistence is owned by the adapter's `datetime_range` column
 * type (`coolms/core-doctrine` supplies it).
 */
final readonly class DateTimeRange
{
    public function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
        public bool $includesEnd = true,
    ) {
        if ($includesEnd) {
            if ($end < $start) {
                throw new InvalidRangeException(sprintf('DateTimeRange.end (%s) must be >= start (%s) for inclusive ranges.', $end->format(DateTimeInterface::ATOM), $start->format(DateTimeInterface::ATOM)));
            }
        } elseif ($end <= $start) {
            throw new InvalidRangeException(sprintf('DateTimeRange.end (%s) must be > start (%s) for half-open ranges.', $end->format(DateTimeInterface::ATOM), $start->format(DateTimeInterface::ATOM)));
        }
    }

    /**
     * Reconstruct from the wire-format produced by {@see self::toArray}.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $start = $data['start'] ?? null;
        $end = $data['end'] ?? null;
        if (!is_string($start) || !is_string($end)) {
            throw new InvalidRangeException('DateTimeRange.fromArray requires string start + end.');
        }

        try {
            $startDt = new DateTimeImmutable($start);
            $endDt = new DateTimeImmutable($end);
        } catch (Exception $e) {
            throw new InvalidRangeException(sprintf('DateTimeRange endpoints must be valid ISO-8601, got "%s" / "%s": %s', $start, $end, $e->getMessage()), 0, $e);
        }

        $includesEnd = $data['includesEnd'] ?? true;
        if (!is_bool($includesEnd)) {
            throw new InvalidRangeException('DateTimeRange.includesEnd must be bool when present.');
        }

        return new self($startDt, $endDt, $includesEnd);
    }

    /**
     * True when `$moment` falls within the range, honouring `includesEnd`.
     * Comparisons use the underlying instant -- cross-zone safe.
     */
    public function contains(DateTimeInterface $moment): bool
    {
        if ($moment < $this->start) {
            return false;
        }

        return $this->includesEnd ? $moment <= $this->end : $moment < $this->end;
    }

    /**
     * Duration of the window in whole seconds. End-instant-relative
     * (not honouring `includesEnd`) -- callers usually want the "length
     * of the open interval", and a closed `[A, A]` correctly reports 0.
     */
    public function durationInSeconds(): int
    {
        return $this->end->getTimestamp() - $this->start->getTimestamp();
    }

    /**
     * True iff the two windows share at least one instant. Treats
     * `includesEnd: true` boundaries as touching = overlapping.
     */
    public function overlaps(self $other): bool
    {
        // Instant-touch at the boundary counts as overlap when either
        // side is inclusive -- conservative for "did anything happen
        // in both windows?" semantics.
        $aTouchesEnd = $this->includesEnd;
        $bTouchesEnd = $other->includesEnd;

        $startCmp = $this->start <=> $other->end;
        $endCmp = $other->start <=> $this->end;

        return
            ($startCmp < 0 || (0 === $startCmp && $bTouchesEnd))
            && ($endCmp < 0 || (0 === $endCmp && $aTouchesEnd));
    }

    /**
     * Wire-format JSON representation. Endpoints are emitted as RFC
     * 3339 / ISO-8601 with offset (`Y-m-d\TH:i:sP`), preserving the
     * original zone -- the form the adapter round-trips through.
     *
     * @return array{start: string, end: string, includesEnd: bool}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->format(DateTimeInterface::ATOM),
            'end' => $this->end->format(DateTimeInterface::ATOM),
            'includesEnd' => $this->includesEnd,
        ];
    }
}
