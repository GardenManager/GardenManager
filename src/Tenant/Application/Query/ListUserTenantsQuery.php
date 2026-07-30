<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\UserTenantView;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<list<UserTenantView>> */
#[NoPermissionRequired(
    reason: 'Cross-tenant by design: powers the tenant switcher; results are scoped to the requesting userId.',
)]
final readonly class ListUserTenantsQuery implements QueryInterface
{
    public function __construct(
        public Ulid $userId,
    ) {
    }
}
