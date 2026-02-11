<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Domain\AuthOidc;
use Symfony\Component\Uid\Ulid;

final readonly class OidcLinkView
{
    public function __construct(
        public Ulid $userId,
        public string $userEmail,
        public string $provider,
        public string $subject,
    ) {
    }

    public static function fromEntity(AuthOidc $link): self
    {
        return new self(
            $link->getUser()->getId(),
            $link->getUser()->getEmail(),
            $link->getProvider(),
            $link->getSubject(),
        );
    }
}
