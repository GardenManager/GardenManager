<?php

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdatePlantCommandHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private PlantAccessChecker $plantAccessChecker,
    ) {
    }

    public function __invoke(UpdatePlantCommand $command): void
    {
        $plant = $this->plantRepository->getById($command->plantId);
        $this->plantAccessChecker->ensureOwnership($plant, $command->ownerId);

        $plant->update(
            plantId: $command->plantId,
            ownerId: $command->ownerId,
            localName: $command->localName,
            isHybrid: $command->isHybrid,
            lifecycle: $command->lifecycle,
            genus: $command->genus,
            epithet: $command->epithet,
            cultivar: $command->cultivar,
        );

        $this->plantRepository->save($plant);
    }
}
