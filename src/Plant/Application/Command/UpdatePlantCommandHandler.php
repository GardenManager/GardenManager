<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdatePlantCommandHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private TenantAccessChecker $tenantAccessChecker,
    ) {
    }

    public function __invoke(UpdatePlantCommand $command): void
    {
        $plant = $this->plantRepository->getById($command->plantId);
        $this->tenantAccessChecker->ensureTenantAccess($plant, $command->tenantId);

        $plant->update(
            plantId: $command->plantId,
            tenantId: $command->tenantId,
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
