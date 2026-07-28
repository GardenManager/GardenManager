<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveMemberCommandHandler
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private TenantRepositoryInterface $tenantRepository,
        private PermissionCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function __invoke(RemoveMemberCommand $command): void
    {
        $revokedMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->memberUserId);

        if ($revokedMembership === null) {
            throw TenantException::notAMember($command->tenantId, $command->memberUserId);
        }

        if ($revokedMembership->isOwner()) {
            $allMembers = $this->membershipRepository->findByTenantId($command->tenantId);
            $ownerCount = \count(
                array_filter(
                    $allMembers,
                    static fn (TenantMembership $member): bool => $member->isOwner(),
                ),
            );

            if ($ownerCount <= 1) {
                throw TenantException::cannotRemoveLastOwner($command->tenantId);
            }
        }

        $tenant = $this->tenantRepository->getById($command->tenantId);
        $config = $tenant->getPermissionsConfig()->withoutUserAssignments((string) $command->memberUserId);
        $tenant->updatePermissionsConfig($config);

        $this->tenantRepository->save($tenant);
        $this->membershipRepository->remove($revokedMembership);
        $this->cacheInvalidator->invalidateForTenant($command->tenantId);
    }
}
