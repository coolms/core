<?php

declare(strict_types=1);

namespace CoolMS\Core\ValueObject;

use CoolMS\Core\Exception\InvalidRangeException;

/**
 * Wall-clock time of day, independent of date or timezone.
 *
 * Modelled as separate `hour`/`minute`/`second` integers rather than a
 * naive `DateTimeImmutable` epoch hack to make the "no date / no zone"
 * semantic explicit -- this is a *time of day*, not a moment.
 *
 * Used as a building block for {@see TimeRange} (e.g. working hours
 * windows, business-hours rules, recurring time-of-day events) and any
 * other primitive that needs a "what time is it on the clock face?"
 * value without a date anchor.
 *
 * **Format**: `HH:MM` or `HH:MM:SS` (24-hour, zero-padded). The shorter
 * form is preferred for JSON storage when seconds are zero (round-trip
 * stability vs admin-readable column data).
 *
 * **Range**: hour 0..23, minute 0..59, second 0..59. Leap-second 60 is
 * not modelled; this is a wall-clock vocabulary, not a UTC instant.
 *
 * **Comparison**: lexicographic on the canonical zero-padded string
 * representation is equivalent to numeric clock ordering, but the VO
 * also exposes {@see self::compareTo} for explicit ordering.
 */
final readonly class TimeOfDay
{
    private const string PATTERN_HHMM = '/^([01][0-9]|2[0-3]):([0-5][0-9])$/';

    private const string PATTERN_HHMMSS = '/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';

    public function __construct(
        public int $hour,
        public int $minute,
        public int $second = 0,
    ) {
        if ($hour < 0 || $hour > 23) {
            throw new InvalidRangeException(sprintf('TimeOfDay.hour must be in 0..23, got %d.', $hour));
        }
        if ($minute < 0 || $minute > 59) {
            throw new InvalidRangeException(sprintf('TimeOfDay.minute must be in 0..59, got %d.', $minute));
        }
        if ($second < 0 || $second > 59) {
            throw new InvalidRangeException(sprintf('TimeOfDay.second must be in 0..59, got %d.', $second));
        }
    }

    /**
     * Parse a `HH:MM` or `HH:MM:SS` literal into a {@see TimeOfDay}.
     */
    public static function fromString(string $hhmm): self
    {
        if (1 === preg_match(self::PATTERN_HHMMSS, $hhmm, $m)) {
            return new self((int) $m[1], (int) $m[2], (int) $m[3]);
        }
        if (1 === preg_match(self::PATTERN_HHMM, $hhmm, $m)) {
            return new self((int) $m[1], (int) $m[2], 0);
        }

        throw new InvalidRangeException(sprintf('TimeOfDay must match HH:MM or HH:MM:SS, got "%s".', $hhmm));
    }

    /**
     * Canonical zero-padded string. `HH:MM` when seconds are zero,
     * `HH:MM:SS` otherwise -- the shorter form is the common case for
     * working-hours storage and admin-readable column data.
     */
    public function toString(): string
    {
        return 0 === $this->second
            ? sprintf('%02d:%02d', $this->hour, $this->minute)
            : sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
    }

    /**
     * spaceship-style ordering. -1 if `$this < $other`, 0 if equal, 1
     * if greater.
     */
    public function compareTo(self $other): int
    {
        return $this->totalSeconds() <=> $other->totalSeconds();
    }

    /**
     * Seconds since midnight (0..86_399). Convenience for arithmetic;
     * not part of the persisted form.
     */
    public function totalSeconds(): int
    {
        return ($this->hour * 3600) + ($this->minute * 60) + $this->second;
    }
}
