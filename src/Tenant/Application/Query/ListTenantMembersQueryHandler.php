<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Application\View\TenantMembershipView;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListTenantMembersQueryHandler
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private MemberUserResolverInterface $memberUserResolver,
        private TenantRepositoryInterface $tenantRepository,
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
                $memberships,
            ),
        );

        $tenant = $this->tenantRepository->getById($query->tenantId);
        $config = $tenant->getPermissionsConfig();

        // Build a map of userId -> group name for display
        $groupNamesByUserId = [];
        foreach ($memberships as $membership) {
            $assignedSlugs = $config->getUserAssignments((string) $membership->getUserId());
            $groupNames = [];
            foreach ($assignedSlugs as $slug) {
                $group = $config->getGroup($slug);
                $groupNames[] = $group !== null ? $group->name : $slug;
            }
            $groupNamesByUserId[(string) $membership->getUserId()] = implode(', ', $groupNames) ?: 'None';
        }

        return array_map(
            static function (TenantMembership $membership) use ($usersById, $groupNamesByUserId): TenantMembershipView {
                $user = $usersById[(string) $membership->getUserId()];
                $groupName = $groupNamesByUserId[(string) $membership->getUserId()] ?? 'None';

                return TenantMembershipView::fromMembershipAndUser($membership, $user, $groupName);
            }, $memberships,
        );
    }
}
