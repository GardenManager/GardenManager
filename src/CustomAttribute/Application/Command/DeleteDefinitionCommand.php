<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(CustomAttributePermissions::DELETE)]
final readonly class DeleteDefinitionCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $definitionId,
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
