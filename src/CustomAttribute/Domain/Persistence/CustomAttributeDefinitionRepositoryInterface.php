<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Persistence;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

interface CustomAttributeDefinitionRepositoryInterface
{
    public function getById(Ulid $id): CustomAttributeDefinition;

    /** @return list<CustomAttributeDefinition> */
    public function findByEntityType(string $entityType): array;

    /** @return PaginatedResult<CustomAttributeDefinition> */
    public function findPaginatedByEntityType(?string $entityType, int $page, int $perPage): PaginatedResult;

    public function existsByEntityTypeAndName(string $entityType, string $name): bool;

    public function save(CustomAttributeDefinition $definition): void;

    public function remove(CustomAttributeDefinition $definition): void;
}
