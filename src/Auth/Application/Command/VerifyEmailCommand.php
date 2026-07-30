<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

#[NoPermissionRequired(reason: 'Pre-authentication: the email verification link is followed before login.')]
final readonly class VerifyEmailCommand implements CommandInterface
{
    public function __construct(
        public Ulid $userId,
    ) {
    }
}
