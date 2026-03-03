<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Permission\Application\Service\DefaultGroupProvisioningService;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateTenantCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantMembershipRepositoryInterface $membershipRepository,
        private DefaultGroupProvisioningService $provisioningService,
    ) {
    }

    public function __invoke(CreateTenantCommand $command): void
    {
        $tenant = Tenant::create(
            name: $command->name,
            id: $command->tenantId,
        );

        $config = $this->provisioningService->provisionDefaultGroups()
            ->withUserAssignments((string) $command->userId, ['admin']);
        $tenant->updatePermissionsConfig($config);

        $this->tenantRepository->save($tenant);

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $command->userId,
            isOwner: true,
        );

        $this->membershipRepository->save($membership);
    }
}
