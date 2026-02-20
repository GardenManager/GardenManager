<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTenantCommand implements CommandInterface
{
    public function __construct(
        public Ulid $tenantId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        public Ulid $actorUserId,
    ) {
    }
}
