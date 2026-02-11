<?php

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

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
