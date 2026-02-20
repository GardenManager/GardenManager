<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Service;

use GardenManager\Shared\Application\CommandDispatcherInterface;
use GardenManager\Tenant\Application\Command\CreateTenantCommand;
use Symfony\Component\Uid\Ulid;

final readonly class TenantProvisioningService
{
    public function __construct(
        private CommandDispatcherInterface $commandDispatcher,
    ) {
    }

    public function provisionPersonalTenant(Ulid $userId, string $tenantName): void
    {
        $this->commandDispatcher->dispatchCommand(new CreateTenantCommand(
            tenantId: $userId,
            userId: $userId,
            name: $tenantName,
        ));
    }
}
