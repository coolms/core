<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use RuntimeException;

use function sprintf;

/**
 * The requested definition key is unknown to the addressed module.
 *
 * Distinct from {@see DefinitionLifecycleRefused}: nothing exists to
 * act on, so this maps to HTTP 404 rather than 409.
 */
final class DefinitionNotFound extends RuntimeException
{
    public function __construct(
        public readonly string $definitionKey,
        public readonly string $module,
    ) {
        parent::__construct(sprintf(
            'No %s definition with key "%s".',
            $module,
            $definitionKey,
        ));
    }
}
