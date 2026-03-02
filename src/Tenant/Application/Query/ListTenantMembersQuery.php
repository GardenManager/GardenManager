<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\TenantMembershipView;
use GardenManager\Tenant\Domain\MemberPermissions;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<list<TenantMembershipView>> */
#[RequiresPermission(MemberPermissions::LIST)]
final readonly class ListTenantMembersQuery implements QueryInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $actorUserId,
    ) {
    }

    public function getActorUserId(): Ulid
    {
        return $this->actorUserId;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }
}
