<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Application\View\PlantDetailView;
use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;

/** @implements PaginatedQueryInterface<PaginatedResult<PlantDetailView>> */
final readonly class ListPlantsQuery implements PaginatedQueryInterface
{
    public const int DEFAULT_LIMIT = 10;

    public function __construct(
        private int $page = 1,
        private int $limit = self::DEFAULT_LIMIT,
    ) {
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
