<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[NoPermissionRequired(reason: 'Pre-authentication: self-registration runs before any actor or tenant exists.')]
final readonly class RegisterUserCommand implements CommandInterface
{
    public function __construct(
        public Ulid $userId,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $displayName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $plainPassword,
    ) {
    }
}
