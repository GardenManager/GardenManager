<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Query;

use GardenManager\Permission\Application\View\MemberPermissionOverrideView;
use GardenManager\Permission\Application\View\MemberPermissionView;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetMemberPermissionDetailQueryHandler
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private MemberUserResolverInterface $memberUserResolver,
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function __invoke(GetMemberPermissionDetailQuery $query): MemberPermissionView
    {
        $membership = $this->membershipRepository->findByTenantIdAndUserId($query->tenantId, $query->userId);

        if ($membership === null) {
            throw TenantException::notAMember($query->tenantId, $query->userId);
        }

        $usersById = $this->memberUserResolver->resolveByIds([$query->userId]);
        $userInfo = $usersById[$query->userId->toString()];
        $tenant = $this->tenantRepository->getById($query->tenantId);
        $config = $tenant->getPermissionsConfig();
        $groupSlugs = $config->getUserAssignments($query->userId->toString());
        $overrideEntries = $config->getUserOverrides($query->userId->toString());
        $overrideViews = array_map(
            static fn (string $entry): MemberPermissionOverrideView => MemberPermissionOverrideView::fromData(
                (string) $query->userId,
                $entry,
            ),
            $overrideEntries,
        );

        return new MemberPermissionView(
            userId: $query->userId,
            userEmail: $userInfo->email,
            userDisplayName: $userInfo->displayName,
            isOwner: $membership->isOwner(),
            joinedAt: $membership->getCreatedAt(),
            groupSlugs: $groupSlugs,
            overrides: $overrideViews,
        );
    }
}
