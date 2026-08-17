<?php

declare(strict_types=1);

namespace CoolMS\Core\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Inclusive- or half-open- range of dates (no time-of-day, no zone).
 *
 * Both endpoints are normalised to date-only via the `Y-m-d` literal
 * representation -- the input `DateTimeImmutable`s' time-of-day and
 * timezone are intentionally dropped because a date range is a
 * calendar-clock concept, not a moment range. Consumers that need
 * moment semantics should reach for {@see DateTimeRange}.
 *
 * **`$includesEnd` default**: `true` (closed range `[start, end]`).
 * This matches the most common business intuition ("the contract runs
 * May 1 to May 31 inclusive"), at the cost of being slightly awkward
 * for `[start, start)` "empty" semantics. Half-open `[start, end)` is
 * available by passing `includesEnd: false`.
 *
 * **Storage**: serialised to JSON via {@see self::toArray} as
 * `{"start": "YYYY-MM-DD", "end": "YYYY-MM-DD", "includesEnd": bool}`.
 * Persistence is owned by the adapter's `date_range` column type
 * (`coolms/core-doctrine` supplies it).
 */
final readonly class DateRange
{
    public DateTimeImmutable $start;
    public DateTimeImmutable $end;

    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        public bool $includesEnd = true,
    ) {
        // Normalise to date-only midnight UTC; the date itself is
        // what's load-bearing, the rest is noise we don't want
        // round-tripping through JSON.
        $this->start = self::truncateToDate($start);
        $this->end = self::truncateToDate($end);

        if ($includesEnd) {
            if ($this->end < $this->start) {
                throw new InvalidRangeException(sprintf('DateRange.end (%s) must be >= start (%s) for inclusive ranges.', $this->end->format('Y-m-d'), $this->start->format('Y-m-d')));
            }
        } elseif ($this->end <= $this->start) {
            throw new InvalidRangeException(sprintf('DateRange.end (%s) must be > start (%s) for half-open ranges.', $this->end->format('Y-m-d'), $this->start->format('Y-m-d')));
        }
    }

    /**
     * Reconstruct from the wire-format produced by {@see self::toArray}.
     * Tolerates `includesEnd` absent (defaults to `true`).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $start = $data['start'] ?? null;
        $end = $data['end'] ?? null;
        if (!is_string($start) || !is_string($end)) {
            throw new InvalidRangeException('DateRange.fromArray requires string start + end.');
        }

        $startDt = DateTimeImmutable::createFromFormat('!Y-m-d', $start);
        $endDt = DateTimeImmutable::createFromFormat('!Y-m-d', $end);
        if (false === $startDt || false === $endDt) {
            throw new InvalidRangeException(sprintf('DateRange endpoints must be Y-m-d strings, got "%s" / "%s".', $start, $end));
        }

        $includesEnd = $data['includesEnd'] ?? true;
        if (!is_bool($includesEnd)) {
            throw new InvalidRangeException('DateRange.includesEnd must be bool when present.');
        }

        return new self($startDt, $endDt, $includesEnd);
    }

    /**
     * True when `$date` falls within the range, honouring `includesEnd`.
     * Only the calendar-day component of `$date` is considered.
     */
    public function contains(DateTimeInterface $date): bool
    {
        $d = self::truncateToDate(DateTimeImmutable::createFromInterface($date));
        if ($d < $this->start) {
            return false;
        }

        return $this->includesEnd ? $d <= $this->end : $d < $this->end;
    }

    /**
     * Number of calendar days covered.
     *  - Closed range `[A, A]`             -> 1
     *  - Closed range `[A, A + 1 day]`     -> 2
     *  - Half-open `[A, A + 1 day)`        -> 1
     *  - Half-open `[A, A)`                -> not constructible (constructor rejects).
     */
    public function durationInDays(): int
    {
        $diff = (int) $this->start->diff($this->end)->format('%r%a');

        return $this->includesEnd ? $diff + 1 : $diff;
    }

    /**
     * True iff the two ranges share at least one calendar day. Honours
     * each side's `includesEnd` independently.
     */
    public function overlaps(self $other): bool
    {
        // [aS, aE]/[aS, aE) overlaps [bS, bE]/[bS, bE) iff
        // aS <= bE'  AND  bS <= aE'
        // where xE' is xE when includesEnd, else xE - 1 day.
        $aEnd = $this->includesEnd ? $this->end : $this->end->modify('-1 day');
        $bEnd = $other->includesEnd ? $other->end : $other->end->modify('-1 day');

        return $this->start <= $bEnd && $other->start <= $aEnd;
    }

    /**
     * Wire-format JSON representation.
     *
     * @return array{start: string, end: string, includesEnd: bool}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->format('Y-m-d'),
            'end' => $this->end->format('Y-m-d'),
            'includesEnd' => $this->includesEnd,
        ];
    }

    private static function truncateToDate(DateTimeImmutable $dt): DateTimeImmutable
    {
        // Strip time-of-day + force UTC so equality / comparison is
        // stable regardless of input zone.
        return new DateTimeImmutable(
            $dt->format('Y-m-d') . 'T00:00:00+00:00',
        );
    }
}
