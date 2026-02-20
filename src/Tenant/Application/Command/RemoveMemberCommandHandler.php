<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveMemberCommandHandler
{
    public function __construct(private TenantMembershipRepositoryInterface $membershipRepository)
    {
    }

    public function __invoke(RemoveMemberCommand $command): void
    {
        $actorMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->actorUserId);

        if ($actorMembership === null || $actorMembership->getRole() !== TenantMembershipRole::OWNER) {
            throw TenantException::onlyOwnersCanManage($command->tenantId, $command->actorUserId);
        }

        $revokedMembership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->memberUserId);

        if ($revokedMembership === null) {
            throw TenantException::notAMember($command->tenantId, $command->memberUserId);
        }

        if ($revokedMembership->getRole() === TenantMembershipRole::OWNER) {
            $allMembers = $this->membershipRepository->findByTenantId($command->tenantId);
            $ownerCount = count(array_filter($allMembers, function (TenantMembership $member): bool {
                return $member->getRole() === TenantMembershipRole::OWNER;
            }));

            if ($ownerCount <= 1) {
                throw TenantException::cannotRemoveLastOwner($command->tenantId);
            }
        }

        $this->membershipRepository->remove($revokedMembership);
    }
}
