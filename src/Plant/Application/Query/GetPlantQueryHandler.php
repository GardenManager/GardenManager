<?php

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetPlantQueryHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private PlantAccessChecker $plantAccessChecker,
    ) {
    }

    public function __invoke(GetPlantQuery $query): PlantDetailView
    {
        $plant = $this->plantRepository->getById($query->plantId);
        $this->plantAccessChecker->ensureOwnership($plant, $query->ownerId);

        return PlantDetailView::fromEntity($plant);
    }
}
