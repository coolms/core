<?php

declare(strict_types=1);

namespace CoolMS\Core\Repository;

use CoolMS\Core\Identifier\IdentifierProviderInterface;
use Symfony\Component\Uid\Uuid;

interface RepositoryInterface
{
    /**
     * @return IdentifierProviderInterface|null
     */
    public function find(Uuid $id): ?object;

    /**
     * @return iterable<IdentifierProviderInterface>
     */
    public function findAll(): iterable;

    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return iterable<IdentifierProviderInterface>
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): iterable;

    /**
     * @param array<string, mixed> $criteria
     *
     * @return IdentifierProviderInterface|null
     */
    public function findOneBy(array $criteria): ?object;

    public function save(IdentifierProviderInterface $entity): void;

    public function delete(IdentifierProviderInterface $entity): void;

    public function refresh(IdentifierProviderInterface $entity): void;

    public function count(): int;
}
