<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Security\TenantAuthorizationChecker;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class InviteMemberCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantMembershipRepositoryInterface $membershipRepository,
        private MemberUserResolverInterface $memberUserResolver,
        private TenantAuthorizationChecker $authorizationChecker,
    ) {
    }

    public function __invoke(InviteMemberCommand $command): void
    {
        $this->authorizationChecker->ensureOwner($command->tenantId, $command->actorUserId);

        $invitee = $this->memberUserResolver->resolveByEmail($command->inviteeEmail);

        if ($invitee === null) {
            throw TenantException::inviteeNotFound($command->inviteeEmail);
        }

        $existingMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $invitee->id);

        if ($existingMembership !== null) {
            throw TenantException::alreadyAMember($command->tenantId, $command->inviteeEmail);
        }

        $tenant = $this->tenantRepository->getById($command->tenantId);

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $invitee->id,
            role: $command->role,
            id: $command->membershipId,
        );

        $this->membershipRepository->save($membership);
    }
}
