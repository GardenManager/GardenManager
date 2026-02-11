<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Domain\AuthUser;
use Symfony\Component\Uid\Ulid;

final readonly class AuthUserSummaryView
{
    public function __construct(
        public Ulid $id,
        public string $email,
        public string $displayName,
        public bool $hasPassword,
        public bool $isVerified,
    ) {
    }

    public static function fromEntity(AuthUser $user): self
    {
        return new self(
            $user->getId(),
            $user->getEmail(),
            $user->getDisplayName(),
            $user->hasPassword(),
            $user->isVerified(),
        );
    }
}
