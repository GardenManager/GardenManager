<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[NoPermissionRequired(reason: 'Pre-authentication: OIDC account linking runs during the login flow.')]
final readonly class CreateOidcLinkCommand implements CommandInterface
{
    public function __construct(
        public Ulid $linkId,
        public Ulid $userId,

        #[Assert\NotBlank]
        public string $provider,

        #[Assert\NotBlank]
        public string $subject,
    ) {
    }
}
