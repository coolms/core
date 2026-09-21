<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

/**
 * One entry, one verdict, and the sentence an operator reads: the module,
 * the contract and range it offers, the theme and the version it implements.
 */
final readonly class UiEntryVerdict
{
    public function __construct(
        public UiEntry $entry,
        public UiVerdict $verdict,
        public string $reason,
    ) {
    }
}
