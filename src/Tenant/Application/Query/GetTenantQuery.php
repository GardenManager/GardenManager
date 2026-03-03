<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Query;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Tenant\Application\View\TenantDetailView;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<TenantDetailView> */
#[RequiresPermission(TenantPermissions::VIEW)]
final readonly class GetTenantQuery implements QueryInterface, AuthorizedMessageInterface
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
