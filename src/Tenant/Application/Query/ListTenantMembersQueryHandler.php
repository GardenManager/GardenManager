<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Tenant\Application\View\TenantMembershipView;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListTenantMembersQueryHandler
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private AuthUserRepositoryInterface $userRepository,
    ) {
    }

    /** @return list<TenantMembershipView> */
    public function __invoke(ListTenantMembersQuery $query): array
    {
        $actorMembership = $this->membershipRepository->findByTenantIdAndUserId($query->tenantId, $query->actorUserId);

        if ($actorMembership === null) {
            throw TenantException::notAMember($query->tenantId, $query->actorUserId);
        }

        $memberships = $this->membershipRepository->findByTenantId($query->tenantId);

        return array_map(function (TenantMembership $membership): TenantMembershipView {
            $user = $this->userRepository->getById($membership->getUserId());

            return TenantMembershipView::fromMembershipAndUser($membership, $user);
        }, $memberships);
    }
}
