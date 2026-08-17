<?php

declare(strict_types=1);

namespace CoolMS\Core\Mapping;

use Attribute;

/**
 * Declares a persisted column without naming a persistence library.
 *
 * The reusable traits carry these instead of an ORM's own mapping
 * attributes, so this package depends on no persistence library at all.
 * A mapping driver inside the persistence adapter translates them.
 *
 * The vocabulary is deliberately the small one the traits actually use: the
 * moment this grows associations or inheritance it stops being a translation
 * and becomes a second mapping layer to maintain.
 *
 * `$type` is a logical name the adapter resolves. The usual ORM names are
 * used verbatim where they fit ('string', 'json', 'datetime_immutable') because
 * inventing a parallel vocabulary would buy nothing and cost a lookup table.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Column
{
    /**
     * @param array<string, mixed> $options adapter-specific extras, e.g. ['default' => 0]
     */
    public function __construct(
        public ?string $name = null,
        public string $type = 'string',
        public bool $nullable = false,
        public bool $unique = false,
        public ?int $length = null,
        public array $options = [],
        public ?string $enumType = null,
    ) {
    }
}
