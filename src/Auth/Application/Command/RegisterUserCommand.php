<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

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
