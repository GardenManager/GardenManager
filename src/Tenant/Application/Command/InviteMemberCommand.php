<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Tenant\Domain\MemberPermissions;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[RequiresPermission(MemberPermissions::INVITE)]
final readonly class InviteMemberCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $membershipId,
        public Ulid $tenantId,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $inviteeEmail,
        public string $groupSlug,
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
