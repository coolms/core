<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use Traversable;

use function array_key_exists;
use function count;
use function is_string;

/**
 * Read-only `name -> VariableDeclaration` map for `process.variables{}`.
 * Implements `ArrayAccess` for ergonomic `$vars['userId']` lookups in
 * validator rules and engine code; mutation methods throw because the
 * AST is immutable post-parse.
 *
 * `offsetGet` returns `null` on miss (cheaper than catching) so callers
 * can chain `?? throw`. Counts and iteration follow underlying array
 * order, which equals JSON source order -- the parser does not sort.
 *
 * @implements ArrayAccess<string, VariableDeclaration>
 * @implements IteratorAggregate<string, VariableDeclaration>
 */
final readonly class VariableMap implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<string, VariableDeclaration> */
    private array $vars;

    /**
     * @param array<string, VariableDeclaration> $vars
     */
    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    public function offsetExists(mixed $offset): bool
    {
        // Per the @implements ArrayAccess<string, ...> contract callers
        // pass strings; defensive runtime check kept against
        // bad-actor code paths but invisible to phpstan.
        /* @phpstan-ignore-next-line function.alreadyNarrowedType */
        return is_string($offset) && array_key_exists($offset, $this->vars);
    }

    public function offsetGet(mixed $offset): ?VariableDeclaration
    {
        /* @phpstan-ignore-next-line function.alreadyNarrowedType */
        if (!is_string($offset)) {
            return null;
        }

        return $this->vars[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException('VariableMap is immutable.');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException('VariableMap is immutable.');
    }

    public function count(): int
    {
        return count($this->vars);
    }

    /**
     * @return Traversable<string, VariableDeclaration>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->vars);
    }
}
