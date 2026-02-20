<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\TenantMembershipView;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<list<TenantMembershipView>> */
final readonly class ListTenantMembersQuery implements QueryInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $actorUserId,
    ) {
    }
}
