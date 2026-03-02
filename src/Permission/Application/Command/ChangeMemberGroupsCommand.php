<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[RequiresPermission(TenantPermissions::EDIT)]
final readonly class ChangeMemberGroupsCommand implements CommandInterface, AuthorizedMessageInterface
{
    /**
     * @param list<string> $groupSlugs
     */
    public function __construct(
        public Ulid $tenantId,
        public Ulid $userId,

        #[Assert\Count(min: 1, minMessage: 'At least one group must be selected.')]
        public array $groupSlugs,

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
