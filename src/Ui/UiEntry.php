<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * One entry a module offers for a host contract, as its `config/ui.yaml`
 * declares it: which contract, which versions of it, on which framework,
 * and where the entry file is.
 *
 * A module never REQUIRES a contract. An entry a theme does not read is
 * unused and the module still installs; an entry a theme reads at a version
 * the range does not include is refused by name (see {@see UiContractMatcher}).
 * The range is a caret over MAJOR.MINOR (`^1.2`): the entry uses extension
 * points the contract gained by 1.2, so a host at 1.1 is refused rather than
 * silently missing them, and a host at 2.0 is refused because a major
 * changes or removes points.
 */
final readonly class UiEntry
{
    public function __construct(
        /** The module id, as `config/modules/<id>`. */
        public string $module,
        /** `console`, `desk`, `site`. */
        public string $contract,
        /** `^MAJOR.MINOR`. Kept as written; {@see admits()} reads it. */
        public string $range,
        /** `angular`, `react` -- the compatibility fact of the entry file. */
        public string $framework,
        /** Path of the entry file, relative to the module's root. */
        public string $entry,
    ) {
        if (1 !== preg_match('/^\^\d+\.\d+$/', $range)) {
            throw new InvalidArgumentException(sprintf("Module '%s' offers %s with range '%s'; a range is '^MAJOR.MINOR'.", $module, $contract, $range));
        }
    }

    /**
     * Whether this entry's range includes a contract version: same major,
     * minor at least the range's. A version that cannot be read admits nothing.
     */
    public function admits(string $version): bool
    {
        if (1 !== preg_match('/^(\d+)\.(\d+)$/', $version, $v)) {
            return false;
        }
        // The constructor refused any other shape, so this always matches.
        if (1 !== preg_match('/^\^(\d+)\.(\d+)$/', $this->range, $r)) {
            return false;
        }

        return (int) $r[1] === (int) $v[1] && (int) $v[2] >= (int) $r[2];
    }
}
