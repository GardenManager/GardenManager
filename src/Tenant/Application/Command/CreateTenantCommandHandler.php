<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
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
    ) {
    }

    public function __invoke(CreateTenantCommand $command): void
    {
        $tenant = Tenant::create(
            name: $command->name,
            id: $command->tenantId,
        );

        $this->tenantRepository->save($tenant);

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $command->userId,
            role: TenantMembershipRole::OWNER,
        );

        $this->membershipRepository->save($membership);
    }
}
