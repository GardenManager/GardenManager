<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\MemberPermissions;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(MemberPermissions::REMOVE)]
final readonly class RemoveMemberCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $memberUserId,
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
