<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain\Security;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\Uid\Ulid;

final readonly class TenantAuthorizationChecker
{
    public function __construct(private TenantMembershipRepositoryInterface $membershipRepository)
    {
    }

    public function ensureOwner(Ulid $tenantId, Ulid $actorUserId): void
    {
        $membership = $this->membershipRepository->findByTenantIdAndUserId($tenantId, $actorUserId);

        if (
            $membership === null
            || $membership->getRole() !== TenantMembershipRole::OWNER
        ) {
            throw TenantException::onlyOwnersCanManage($tenantId, $actorUserId);
        }
    }
}
