<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\View;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

final readonly class MemberPermissionView
{
    /**
     * @param list<string> $groupSlugs
     * @param list<MemberPermissionOverrideView> $overrides
     */
    public function __construct(
        public Ulid $userId,
        public string $userEmail,
        public string $userDisplayName,
        public bool $isOwner,
        public DateTimeImmutable $joinedAt,
        public array $groupSlugs,
        public array $overrides,
    ) {
    }
}
