<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class InviteMemberCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantMembershipRepositoryInterface $membershipRepository,
        private MemberUserResolverInterface $memberUserResolver,
        private PermissionCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function __invoke(InviteMemberCommand $command): void
    {
        $invitee = $this->memberUserResolver->resolveByEmail($command->inviteeEmail);

        if ($invitee === null) {
            throw TenantException::inviteeNotFound($command->inviteeEmail);
        }

        $existingMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $invitee->id);

        if ($existingMembership !== null) {
            throw TenantException::alreadyAMember($command->tenantId, $command->inviteeEmail);
        }

        $tenant = $this->tenantRepository->getById($command->tenantId);
        $config = $tenant->getPermissionsConfig();

        if (!$config->hasGroup($command->groupSlug)) {
            throw PermissionException::groupNotFound();
        }

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $invitee->id,
            id: $command->membershipId,
        );

        $config = $config->withUserAssignments((string) $invitee->id, [$command->groupSlug]);
        $tenant->updatePermissionsConfig($config);

        $this->tenantRepository->save($tenant);
        $this->membershipRepository->save($membership);
        $this->cacheInvalidator->invalidateForTenant($command->tenantId);
    }
}
