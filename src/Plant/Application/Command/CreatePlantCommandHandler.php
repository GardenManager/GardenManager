<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreatePlantCommandHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
    ) {
    }

    public function __invoke(CreatePlantCommand $command): void
    {
        $this->plantRepository->save(Plant::create(
            tenantId: $command->tenantId,
            localName: $command->localName,
            isHybrid: $command->isHybrid,
            lifecycle: $command->lifecycle,
            genus: $command->genus,
            epithet: $command->epithet,
            cultivar: $command->cultivar,
            plantId: $command->plantId,
        ));
    }
}
