<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Dto;

use Symfony\Component\Uid\Ulid;

final readonly class MemberUserInfoDto
{
    public function __construct(
        public Ulid $id,
        public string $email,
        public string $displayName,
    )
    {
    }
}
