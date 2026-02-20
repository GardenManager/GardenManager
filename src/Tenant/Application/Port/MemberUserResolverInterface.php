<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Port;

use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use Symfony\Component\Uid\Ulid;

interface MemberUserResolverInterface
{
    public function resolveByEmail(string $email): ?MemberUserInfoDto;

    /**
     * @param list<Ulid> $userIds
     *
     * @return array<string, MemberUserInfoDto>
     */
    public function resolveByIds(array $userIds): array;
}
