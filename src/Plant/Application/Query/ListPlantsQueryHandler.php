<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListPlantsQueryHandler
{
    public function __construct(
        private readonly PlantRepositoryInterface $plantRepository,
    ) {
    }

    /** @return PaginatedResult<PlantDetailView> */
    public function __invoke(ListPlantsQuery $query): PaginatedResult
    {
        return $this->plantRepository->findAllByOwnerIdPaginated(
            $query->getOwnerId(),
            $query->getPage(),
            $query->getLimit(),
        )->map(PlantDetailView::fromEntity(...));
    }
}
