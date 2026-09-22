<?php

declare(strict_types=1);

namespace CoolMS\Core\Form;

/**
 * Maps API resource classes to the form ids they declared with {@see FormId}.
 *
 * Filled at compile time by the Form module, read at request time by whatever
 * publishes a resource's form to a client (an API manifest contributor, for
 * one). A reader depends on this contract; the Form module provides the
 * implementation and the alias.
 */
interface FormIdRegistryInterface
{
    /**
     * @param class-string $resourceClass
     */
    public function get(string $resourceClass): ?string;

    /**
     * @return array<class-string, string> resource class => form id
     */
    public function all(): array;
}
