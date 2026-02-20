<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Application\View\PlantDetailView;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetPlantQueryHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private TenantAccessChecker $tenantAccessChecker,
    ) {
    }

    public function __invoke(GetPlantQuery $query): PlantDetailView
    {
        $plant = $this->plantRepository->getById($query->plantId);
        $this->tenantAccessChecker->ensureTenantAccess($plant, $query->tenantId);

        return PlantDetailView::fromEntity($plant);
    }
}
