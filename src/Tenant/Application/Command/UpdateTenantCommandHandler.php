<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateTenantCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function __invoke(UpdateTenantCommand $command): void
    {
        $tenant = $this->tenantRepository->getById($command->tenantId);
        $tenant->update($command->name);
        $this->tenantRepository->save($tenant);
    }
}
