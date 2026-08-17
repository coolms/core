<?php

declare(strict_types=1);

namespace CoolMS\Core\Terminal;

use function array_key_first;
use function array_keys;
use function ksort;

/**
 * Which entities `rql:explain` can inspect — contributed by the modules that
 * own them.
 *
 * ## Why this exists
 *
 * Terminal's `Extension` used to hardcode the catalogue:
 *
 *     $container->setParameter('terminal.rql.entity_map', [
 *         'NaviNode' => NaviNode::class,
 *         'VfsNode'  => Node::class,
 *     ]);
 *
 * — which made the shell import Navi and VFS just to name them, and left the
 * parameter pointing at a dead class the moment either module was deleted. It is
 * the same inversion the commands themselves needed, in a different shape: a map
 * rather than a service, so the tagged-interface route the commands use does not
 * apply.
 *
 * ## Why a registry and not the existing entity-alias registry
 *
 * The entity-alias registry looks like the obvious home and is not: it is
 * specifically the alias map for **ExtrasProvider** entities (dynamic-entity
 * schema + extras resolution). `Node` qualifies;
 * `NaviNode` does not, so registering it there would abuse the registry AND
 * silently drop Navi from `rql:explain`. "Has a dynamic-extras schema" and "can
 * be RQL-inspected" are different questions.
 *
 * ## How a module contributes
 *
 * In the owning module's `Extension::load()` — no attribute, per the standing
 * rule that configurable wiring lives in DI, not in classes:
 *
 *     $container->getDefinition(RqlExplainableEntityRegistry::class)
 *         ->addMethodCall('register', ['VfsNode', Node::class]);
 *
 * Mirrors `EntityAliasRegistry::register` exactly, so there is one shape to
 * learn. Lives in Core (L0) because the contributors are L1/L2 modules and may
 * only import downward.
 */
final class RqlExplainableEntityRegistry
{
    /** @var array<string, class-string> display label => entity class */
    private array $entities = [];

    /**
     * @param class-string $class
     */
    public function register(string $label, string $class): void
    {
        $this->entities[$label] = $class;
    }

    /**
     * @return class-string|null null when the label was never contributed —
     *                           normally because the owning module is not
     *                           installed, which is a legitimate answer rather
     *                           than an error
     */
    public function classFor(string $label): ?string
    {
        return $this->entities[$label] ?? null;
    }

    /**
     * Labels an operator can pass to `--entity`, sorted so the help text and the
     * default do not depend on module registration order.
     *
     * @return list<string>
     */
    public function labels(): array
    {
        $sorted = $this->entities;
        ksort($sorted);

        return array_keys($sorted);
    }

    /**
     * The label used when `--entity` is omitted, or null when nothing at all is
     * registered (every contributing module uninstalled).
     */
    public function defaultLabel(): ?string
    {
        $sorted = $this->entities;
        ksort($sorted);

        return array_key_first($sorted);
    }
}
