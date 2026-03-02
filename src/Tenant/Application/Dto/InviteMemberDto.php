<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class InviteMemberDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $groupSlug = null;
}
