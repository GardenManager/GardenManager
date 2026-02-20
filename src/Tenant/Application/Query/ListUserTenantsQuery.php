<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\UserTenantView;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<list<UserTenantView>> */
final readonly class ListUserTenantsQuery implements QueryInterface
{
    public function __construct(
        public Ulid $userId,
    ) {
    }
}
