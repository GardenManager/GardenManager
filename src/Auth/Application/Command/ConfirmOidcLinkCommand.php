<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[NoPermissionRequired(reason: 'Pre-authentication: OIDC link confirmation runs during the login flow.')]
final readonly class ConfirmOidcLinkCommand implements CommandInterface
{
    public function __construct(
        public Ulid $linkId,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $plainPassword,

        #[Assert\NotBlank]
        public string $provider,

        #[Assert\NotBlank()]
        public string $subject,
    ) {
    }
}
