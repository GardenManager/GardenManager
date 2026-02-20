<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Tenant\Application\View\UserTenantView;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListUserTenantsQueryHandler
{
    public function __construct(private TenantMembershipRepositoryInterface $membershipRepository)
    {
    }

    /** @return list<UserTenantView> */
    public function __invoke(ListUserTenantsQuery $query): array
    {
        $memberships = $this->membershipRepository->findByUserId($query->userId);

        return array_map(
            UserTenantView::fromMembership(...),
            $memberships,
        );
    }
}
