<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Tenant\Application\View\TenantDetailView;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetTenantQueryHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantMembershipRepositoryInterface $membershipRepository,
    ) {
    }

    public function __invoke(GetTenantQuery $query): TenantDetailView
    {
        $membership = $this->membershipRepository->findByTenantIdAndUserId($query->tenantId, $query->actorUserId);

        if ($membership === null) {
            throw TenantException::notAMember($query->tenantId, $query->actorUserId);
        }

        $tenant = $this->tenantRepository->getById($query->tenantId);

        return TenantDetailView::fromEntity($tenant);
    }
}
