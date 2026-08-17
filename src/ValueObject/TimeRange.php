<?php

declare(strict_types=1);

namespace CoolMS\Core\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;

/**
 * Time-of-day window: `[start, end]` clock-face range with no date /
 * no zone semantics. Built on top of {@see TimeOfDay}.
 *
 * Used as a foundation primitive for working-hours / business-hours
 * editors and rules. The calendar module's weekday-hours value object
 * is the existing same-shape primitive in the Calendar module; this
 * Core VO is the generic equivalent and will eventually replace the
 * `from`/`till` string pair (deferred -- the Working Hours refactor is
 * NOT part of this ship).
 *
 * **Invariant**: `end > start`. Equal values (zero-length windows) and
 * inverted values (overnight windows crossing midnight) are explicitly
 * rejected -- if a future caller needs overnight semantics, it should
 * split into two ranges or introduce a separate VO that opts out.
 *
 * **Storage**: serialised to JSON via {@see self::toArray} as
 * `{"start": "HH:MM", "end": "HH:MM"}`. Persistence is owned by
 * the adapter's `time_range` column type (`coolms/core-doctrine`
 * supplies it).
 */
final readonly class TimeRange
{
    public function __construct(
        public TimeOfDay $start,
        public TimeOfDay $end,
    ) {
        if ($end->compareTo($start) <= 0) {
            throw new InvalidRangeException(sprintf('TimeRange.end (%s) must be strictly greater than start (%s).', $end->toString(), $start->toString()));
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
            throw new InvalidRangeException('TimeRange.fromArray requires string start + end.');
        }

        return new self(
            TimeOfDay::fromString($start),
            TimeOfDay::fromString($end),
        );
    }

    /**
     * True when `$time` falls inside the window, half-open
     * `[start, end)` semantics -- the same shape as a working-hours
     * window where 17:00 is closing time, not a working second.
     */
    public function contains(TimeOfDay $time): bool
    {
        return $time->compareTo($this->start) >= 0
            && $time->compareTo($this->end) < 0;
    }

    /**
     * Duration of the window in whole seconds.
     */
    public function durationInSeconds(): int
    {
        return $this->end->totalSeconds() - $this->start->totalSeconds();
    }

    /**
     * True iff the two windows share at least one clock-second of
     * coverage. Half-open semantics -- touching boundaries do NOT
     * overlap (`[09:00, 12:00)` and `[12:00, 17:00)` are disjoint).
     */
    public function overlaps(self $other): bool
    {
        return $this->start->compareTo($other->end) < 0
            && $other->start->compareTo($this->end) < 0;
    }

    /**
     * Wire-format JSON representation.
     *
     * @return array{start: string, end: string}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toString(),
            'end' => $this->end->toString(),
        ];
    }
}
