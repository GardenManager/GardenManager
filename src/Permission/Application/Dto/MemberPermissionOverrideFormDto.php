<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Dto;

use GardenManager\Permission\Application\Validator\ValidPermissionEntry;
use Symfony\Component\Validator\Constraints as Assert;

final class MemberPermissionOverrideFormDto
{
    #[ValidPermissionEntry(prefixed: false)]
    public ?string $permission = null;

    #[Assert\NotNull]
    public ?bool $granted = null;
}
