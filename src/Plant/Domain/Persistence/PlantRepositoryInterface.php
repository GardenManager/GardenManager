<?php

declare(strict_types=1);

namespace GardenManager\Plant\Domain\Persistence;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

interface PlantRepositoryInterface
{
    public function getById(Ulid $plantId): Plant;

    public function findAllByOwnerIdPaginated(Ulid $ownerId, int $page, int $limit): PaginatedResult;

    public function save(Plant $plant): void;
}
