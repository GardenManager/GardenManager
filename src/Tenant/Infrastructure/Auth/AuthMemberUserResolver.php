<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Auth;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;

final readonly class AuthMemberUserResolver implements MemberUserResolverInterface
{
    public function __construct(private AuthUserRepositoryInterface $userRepository)
    {
    }

    public function resolveByEmail(string $email): ?MemberUserInfoDto
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            return null;
        }

        return self::toMemberUserInfo($user);
    }

    public function resolveByIds(array $userIds): array
    {
        $users = $this->userRepository->findByIds($userIds);

        return array_map(self::toMemberUserInfo(...), $users);
    }

    private static function toMemberUserInfo(AuthUser $user): MemberUserInfoDto
    {
        return new MemberUserInfoDto(
            id: $user->getId(),
            email: $user->getEmail(),
            displayName: $user->getDisplayName(),
        );
    }
}
