<?php

declare(strict_types=1);

namespace CoolMS\Core\Field;

use InvalidArgumentException;

use function implode;
use function sprintf;

/**
 * Raised when a field definition asks for a name {@see ReservedFieldNames} holds back.
 *
 * The message carries the reason and the full reserved list, because the caller
 * is usually a person naming a field in an admin form and "reserved" on its own
 * does not tell them what to pick instead.
 */
final class ReservedFieldNameException extends InvalidArgumentException
{
    public static function forField(string $fieldName): self
    {
        return new self(sprintf(
            'Field name "%s" is reserved: %s. Reserved names: %s.',
            $fieldName,
            ReservedFieldNames::getReasonFor($fieldName) ?? 'unknown',
            implode(', ', ReservedFieldNames::all()),
        ));
    }
}
