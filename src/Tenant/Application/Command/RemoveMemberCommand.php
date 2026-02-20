<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

final readonly class RemoveMemberCommand implements CommandInterface
{
    public function __construct(
        public Ulid $tenantId,
        public Ulid $memberUserId,
        public Ulid $actorUserId,
    ) {
    }
}
