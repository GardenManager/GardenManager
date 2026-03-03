<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\TenantPermissions;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[RequiresPermission(TenantPermissions::EDIT)]
final readonly class UpdateTenantCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $tenantId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
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
