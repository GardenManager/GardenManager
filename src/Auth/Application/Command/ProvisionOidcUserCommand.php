<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[NoPermissionRequired(reason: 'Pre-authentication: provisions the user record during first OIDC login.')]
final readonly class ProvisionOidcUserCommand implements CommandInterface
{
    public function __construct(
        public Ulid $userId,
        public Ulid $linkId,

        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $displayName,

        #[Assert\NotBlank]
        public string $provider,

        #[Assert\NotBlank]
        public string $subject,
    ) {
    }
}
