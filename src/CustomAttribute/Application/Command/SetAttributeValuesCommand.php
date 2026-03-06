<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(CustomAttributePermissions::SET_VALUES)]
final readonly class SetAttributeValuesCommand implements CommandInterface, AuthorizedMessageInterface
{
    /**
     * @param array<string, mixed> $values definition ID string => value
     */
    public function __construct(
        public Ulid $tenantId,
        public Ulid $actorUserId,
        public string $entityType,
        public Ulid $entityId,
        public array $values,
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
