<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Workflow\ElementInterface;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

use function array_key_exists;
use function array_values;
use function count;

/**
 * Read-only `id -> ElementInterface` index for everything in the flat
 * `elements[]` JSON list *except* sequence flows and boundary events --
 * those live in dedicated root slots (see
 * `docs/investigations/m2c-design.md` section 3.2).
 *
 * The map underpins `ProcessDefinitionAst::element()` and is the
 * canonical lookup source for the validator's Pass 2 reference-
 * integrity rules. Lookup is O(1).
 *
 * @implements IteratorAggregate<string, ElementInterface>
 */
final readonly class ElementMap implements Countable, IteratorAggregate
{
    /** @var array<string, ElementInterface> */
    private array $elements;

    /**
     * @param array<string, ElementInterface> $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function get(string $id): ?ElementInterface
    {
        return $this->elements[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->elements);
    }

    /**
     * @return list<ElementInterface>
     */
    public function values(): array
    {
        return array_values($this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return Traversable<string, ElementInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}
