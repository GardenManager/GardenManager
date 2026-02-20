<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class InviteMemberCommand implements CommandInterface
{
    public function __construct(
        public Ulid $membershipId,
        public Ulid $tenantId,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $inviteeEmail,
        public TenantMembershipRole $role,
        public Ulid $actorUserId,
    ) {
    }
}
