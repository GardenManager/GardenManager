<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeletePlantCommandHandler
{
    public function __construct(
        private PlantRepositoryInterface $plantRepository,
        private TenantAccessChecker $tenantAccessChecker,
    ) {
    }

    public function __invoke(DeletePlantCommand $command): void
    {
        $plant = $this->plantRepository->getById($command->plantId);
        $this->tenantAccessChecker->ensureTenantAccess($plant, $command->tenantId);

        $plant->softDelete();
        $this->plantRepository->save($plant);
    }
}
