<?php

namespace GardenManager\Plant\Application\Query;

use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PaginatedResult<PlantDetailView>> */
final readonly class ListPlantsQuery implements PaginatedQueryInterface
{
    public const int DEFAULT_LIMIT = 10;

    public function __construct(
        private Ulid $ownerId,
        private int $page = 1,
        private int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public function getOwnerId(): Ulid
    {
        return $this->ownerId;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
