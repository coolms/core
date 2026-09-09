<?php

declare(strict_types=1);
namespace CoolMS\Core\Form;

/**
 * Role-based access policy for a single form field.
 *
 * read:  roles that can SEE this field  ([] = everyone)
 * write: roles that can EDIT this field ([] = same as read)
 *
 * If the current user lacks a required read role,  the field is omitted entirely.
 * If the current user lacks a required write role, the field is rendered readonly.
 */
final readonly class FieldSecurityPolicy
{
    /**
     * @param string[] $read  Roles required to see the field  ([] = public)
     * @param string[] $write Roles required to edit the field ([] = same as read)
     */
    public function __construct(
        public array $read = [],
        public array $write = [],
    ) {
    }

    public function isPublicRead(): bool
    {
        return [] === $this->read;
    }

    public function isPublicWrite(): bool
    {
        return [] === $this->write;
    }
}
