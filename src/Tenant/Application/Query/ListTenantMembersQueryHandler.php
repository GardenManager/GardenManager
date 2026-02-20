<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Application\View\TenantMembershipView;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListTenantMembersQueryHandler
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private MemberUserResolverInterface $memberUserResolver,
    ) {
    }

    /**
     * @return list<TenantMembershipView>
     */
    public function __invoke(ListTenantMembersQuery $query): array
    {
        $actorMembership = $this->membershipRepository->findByTenantIdAndUserId($query->tenantId, $query->actorUserId);

        if ($actorMembership === null) {
            throw TenantException::notAMember($query->tenantId, $query->actorUserId);
        }

        $memberships = $this->membershipRepository->findByTenantId($query->tenantId);
        $usersById = $this->memberUserResolver->resolveByIds(
            array_map(
                static fn (TenantMembership $m): Ulid => $m->getUserId(),
                $memberships
            )
        );

        return array_map(
            function (TenantMembership $membership) use ($usersById): TenantMembershipView {
                $user = $usersById[(string) $membership->getUserId()] ?? null;

                return TenantMembershipView::fromMembershipAndUser($membership, $user);
            }, $memberships
        );
    }
}
