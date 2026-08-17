<?php

declare(strict_types=1);

namespace CoolMS\Core\Mapping;

use Attribute;

/**
 * Marks the property that identifies the record.
 *
 * Pairs with {@see Column}; this only says "and it is the identifier", so the
 * type and column name are declared once, on the Column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Id
{
}
