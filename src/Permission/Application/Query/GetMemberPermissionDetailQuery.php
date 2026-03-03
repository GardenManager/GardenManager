<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Query;

use GardenManager\Permission\Application\View\MemberPermissionView;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<MemberPermissionView> */
#[RequiresPermission(TenantPermissions::VIEW)]
final readonly class GetMemberPermissionDetailQuery implements QueryInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $userId,
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
