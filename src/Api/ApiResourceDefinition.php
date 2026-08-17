<?php

declare(strict_types=1);

namespace CoolMS\Core\Api;

/** Describes a single API endpoint to be represented as a resource node. */
final readonly class ApiResourceDefinition
{
    /**
     * @param string                                                                  $path        Absolute path (route), e.g. '/api/v1/media/assets'
     * @param string                                                                  $ownerAlias  System user module alias, e.g. 'media' or 'root'
     * @param string|null                                                             $groupName   Group name, e.g. 'media_library'; null uses the owner's primary group
     * @param int                                                                     $mode        Unix permission mode, e.g. 0o660
     * @param string                                                                  $description Human-readable purpose stored in extras
     * @param string[]                                                                $httpMethods Allowed HTTP verbs, e.g. ['GET', 'PATCH', 'DELETE']
     * @param array<string, array{type: string, description: string, required: bool}> $routeParams
     *                                                                                             Route placeholder metadata; auto-extracted when not provided
     */
    public function __construct(
        public string $path,
        public string $ownerAlias,
        public ?string $groupName,
        public int $mode,
        public string $description,
        public array $httpMethods,
        public array $routeParams = [],
    ) {
    }
}
