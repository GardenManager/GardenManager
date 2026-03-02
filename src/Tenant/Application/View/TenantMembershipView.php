<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\View;

use DateTimeImmutable;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Domain\TenantMembership;
use Symfony\Component\Uid\Ulid;

final readonly class TenantMembershipView
{
    public function __construct(
        public Ulid $membershipId,
        public Ulid $userId,
        public string $userEmail,
        public string $userDisplayName,
        public bool $isOwner,
        public string $groupName,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromMembershipAndUser(
        TenantMembership $membership,
        MemberUserInfoDto $userInfo,
        string $groupName = '',
    ): self {
        return new self(
            membershipId: $membership->getId(),
            userId: $membership->getUserId(),
            userEmail: $userInfo->email,
            userDisplayName: $userInfo->displayName,
            isOwner: $membership->isOwner(),
            groupName: $groupName,
            createdAt: $membership->getCreatedAt(),
        );
    }
}
