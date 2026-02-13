<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeletePlantCommandHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private PlantAccessChecker $plantAccessChecker,
    ) {
    }

    public function __invoke(DeletePlantCommand $command): void
    {
        $plant = $this->plantRepository->getById($command->plantId);
        $this->plantAccessChecker->ensureOwnership($plant, $command->ownerId);

        $plant->softDelete();
        $this->plantRepository->save($plant);
    }
}
