<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

use function count;

/**
 * Append-only collector of {@see Violation} records, populated by
 * every rule the per-module validator orchestrates.
 *
 * Mutable by design: the orchestrator hands the same instance to
 * every rule via the per-module validation context, each rule
 * appends via {@see add}, and no removal API is exposed. The
 * instance becomes effectively-frozen the moment the orchestrator
 * stops iterating rules; the
 * {@see \CoolMS\Core\Definition\DefinitionValidationException}
 * then captures it on a `readonly` property.
 *
 * Implements `Countable` so callers can `count($violations)` for
 * summary logging, and `IteratorAggregate<int, Violation>` so the
 * orchestrator's `errors()` check and downstream presenters can
 * walk the list once without copying.
 *
 * @implements IteratorAggregate<int, Violation>
 */
final class Violations implements Countable, IteratorAggregate
{
    /** @var list<Violation> */
    private array $items = [];

    public function add(Violation $v): void
    {
        $this->items[] = $v;
    }

    /**
     * @return list<Violation>
     */
    public function asList(): array
    {
        return $this->items;
    }

    public function any(): bool
    {
        return [] !== $this->items;
    }

    /**
     * True if any contained violation has {@see Severity::Error}.
     * The orchestrator uses this to decide whether to throw
     * {@see \CoolMS\Core\Definition\DefinitionValidationException}.
     * Warnings alone are not enough to block deploy.
     */
    public function errors(): bool
    {
        foreach ($this->items as $v) {
            if (Severity::Error === $v->severity) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, Violation>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
