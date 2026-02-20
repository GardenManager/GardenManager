<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateTenantCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantMembershipRepositoryInterface $membershipRepository,
    )
    {
    }

    public function __invoke(UpdateTenantCommand $command): void
    {
        $tenant = $this->tenantRepository->getById($command->tenantId);
        $membership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->actorUserId);

        if ($membership === null || $membership->getRole() !== TenantMembershipRole::OWNER) {
            throw TenantException::onlyOwnersCanManage($command->tenantId, $command->actorUserId);
        }

        $tenant->update($command->name);
        $this->tenantRepository->save($tenant);
    }
}
