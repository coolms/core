<?php

declare(strict_types=1);

namespace CoolMS\Core\Option;

/**
 * Single row in an {@see OptionSourceProviderInterface} response. The
 * universal shape behind every "where do the options for this select
 * come from?" mechanism on the platform — data-grid filter rows, form
 * field selects, autocompletes, etc.
 *
 * Wire-stable: a column / form field references an option source by
 * key (e.g. `calendar.timezones`); the FE picker stores `$value` in
 * the bound model; `$label` is displayed; `$group` lets the picker
 * render grouped sections (e.g. timezones by region); `$description`
 * shows as a hint below the picked option.
 */
final readonly class Option
{
    public function __construct(
        /**
         * Stable identifier persisted by the consumer. Treat it as
         * opaque from the picker's perspective — it's whatever the
         * source declares (UUID, slug, FQCN, enum value, etc.).
         */
        public string $value,
        /** Short human-friendly label shown in the dropdown. */
        public string $label,
        /**
         * Optional grouping bucket. The picker renders contiguous
         * runs with the same `$group` under one heading. `null` means
         * ungrouped (or "default group").
         */
        public ?string $group = null,
        /**
         * Optional one-line hint shown next to / below the option in
         * the dropdown. Useful for things like handler descriptions.
         */
        public ?string $description = null,
    ) {
    }
}
