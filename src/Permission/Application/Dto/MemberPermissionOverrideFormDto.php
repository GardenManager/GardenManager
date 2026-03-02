<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MemberPermissionOverrideFormDto
{
    #[Assert\NotBlank]
    public ?string $permission = null;

    #[Assert\NotNull]
    public ?bool $granted = null;
}
