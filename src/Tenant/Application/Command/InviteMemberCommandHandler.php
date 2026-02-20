<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Auth\Domain\Exception\AuthException;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
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
        private AuthUserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(InviteMemberCommand $command): void
    {
        $actorMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->actorUserId);

        if ($actorMembership === null || $actorMembership->getRole() !== TenantMembershipRole::OWNER) {
            throw TenantException::onlyOwnersCanManage($command->tenantId, $command->actorUserId);
        }

        $invitee = $this->userRepository->findByEmail($command->inviteeEmail);

        if ($invitee === null) {
            throw AuthException::userNotFoundByEmail($command->inviteeEmail);
        }

        $existingMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $invitee->getId());

        if ($existingMembership !== null) {
            throw TenantException::alreadyAMember($command->tenantId, $command->inviteeEmail);
        }

        $tenant = $this->tenantRepository->getById($command->tenantId);

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $invitee->getId(),
            role: $command->role,
            id: $command->membershipId,
        );

        $this->membershipRepository->save($membership);
    }
}
