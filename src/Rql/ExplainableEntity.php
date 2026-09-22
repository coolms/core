<?php

declare(strict_types=1);

namespace CoolMS\Core\Rql;

/**
 * An entity a module offers for RQL inspection, and the tag that carries it.
 *
 * ## Why a registered VALUE rather than a registry
 *
 * "Which entities can be explained" is a catalogue assembled from the modules
 * that own them, and the thing that reads it -- a terminal's `rql-explain`, a
 * documentation page, a future query console -- is not the platform's. So the
 * platform declares the SHAPE and the tag name, each module registers one of
 * these per entity in its own compiler pass, and whoever wants the catalogue
 * collects the tag:
 *
 * ```php
 * $container->register('coolms.rql.explainable.vfs_node', ExplainableEntity::class)
 *     ->setArguments(['VfsNode', Node::class])
 *     ->addTag(ExplainableEntity::TAG);
 * ```
 *
 * A tag rather than method calls on a shared registry, because the registry
 * then belongs to its reader and a module that is removed takes its entry with
 * it -- the failure this replaced was a hardcoded map that outlived the classes
 * it named.
 *
 * !! `ExtrasProvider`'s alias registry is NOT this, and reusing it would be a
 * quiet mistake: it maps entities with a dynamic-extras schema, which some
 * explainable entities are not.
 */
final readonly class ExplainableEntity
{
    /** The tag a module puts on one of these. */
    public const string TAG = 'coolms.rql.explainable';

    public function __construct(
        /** What a person types: `VfsNode`, `NaviNode`. */
        public string $label,
        /** @var class-string the entity the label stands for */
        public string $entityClass,
    ) {
    }
}
