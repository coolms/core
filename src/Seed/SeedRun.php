<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

/**
 * What one seeding run did, in enough detail to report it honestly.
 *
 * ⚠️ Names, not counts. "3 skipped" tells an operator nothing they can act on;
 * `about-us: edited since the last seed` tells them which page holds work they
 * are about to lose and lets them look at it. The whole point of the refusal is
 * defeated by summarising it.
 */
final class SeedRun
{
    /** @var list<string> */
    public array $created = [];

    /** @var list<string> */
    public array $updated = [];

    /** @var list<string> */
    public array $unchanged = [];

    /** @var array<string, string> path => why it was refused */
    public array $refused = [];

    /**
     * ⚠️ Separate from {@see self::$refused} on purpose. A refused edit means
     * somebody's work is safe and the seeder did its job; an occupied path means
     * the seeded page does not exist ANYWHERE and the operator has to choose a
     * different path or move what is there. Counting them together would hide a
     * missing page inside a reassuring number.
     *
     * @var array<string, string> path => what is already there
     */
    public array $occupied = [];

    /** @var array<string, string> path => what was discarded, under --force */
    public array $overwritten = [];

    public function record(string $path, SeedDecision $decision, string $reason = ''): void
    {
        match ($decision) {
            SeedDecision::Create => $this->created[] = $path,
            SeedDecision::Overwrite => $this->updated[] = $path,
            SeedDecision::SkipUnchanged => $this->unchanged[] = $path,
            SeedDecision::RefuseEdited => $this->refused[$path] = $reason,
            SeedDecision::RefuseOccupied => $this->occupied[$path] = $reason,
            SeedDecision::ForcedOverwrite => $this->overwritten[$path] = $reason,
        };
    }

    /**
     * Did anything happen that the operator must be shown by name?
     */
    public function needsAttention(): bool
    {
        return [] !== $this->refused || [] !== $this->occupied || [] !== $this->overwritten;
    }

    public function summary(): string
    {
        return sprintf(
            '%d created, %d updated, %d unchanged, %d refused, %d occupied, %d overwritten',
            count($this->created),
            count($this->updated),
            count($this->unchanged),
            count($this->refused),
            count($this->occupied),
            count($this->overwritten),
        );
    }
}
