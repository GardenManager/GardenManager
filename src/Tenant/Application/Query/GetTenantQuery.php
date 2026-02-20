<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\TenantDetailView;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<TenantDetailView> */
final readonly class GetTenantQuery implements QueryInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $actorUserId,
    ) {
    }
}
