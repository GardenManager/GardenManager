<?php

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

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
