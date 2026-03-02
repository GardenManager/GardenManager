<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use GardenManager\Permission\Infrastructure\Validator\ValidPermissionEntry;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(TenantPermissions::EDIT)]
final readonly class RemoveMemberPermissionOverrideCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $userId,

        #[ValidPermissionEntry(prefixed: false)]
        public string $permission,
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
