<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[NoPermissionRequired(
    reason: 'Bootstrap: the tenant does not exist yet, so no permission can be resolved against it; dispatched only by TenantProvisioningService.',
)]
final readonly class CreateTenantCommand implements CommandInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $userId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
    ) {
    }
}
