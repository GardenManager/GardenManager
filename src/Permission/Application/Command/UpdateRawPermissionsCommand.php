<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(TenantPermissions::EDIT_RAW)]
final readonly class UpdateRawPermissionsCommand implements CommandInterface, AuthorizedMessageInterface
{
    /**
     * @param array<string, mixed> $configData
     */
    public function __construct(
        public Ulid $tenantId,
        public Ulid $actorUserId,
        public array $configData,
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
