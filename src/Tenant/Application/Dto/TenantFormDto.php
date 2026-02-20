<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Dto;

use GardenManager\Tenant\Application\View\TenantDetailView;
use Symfony\Component\Validator\Constraints as Assert;

final class TenantFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    public static function fromView(TenantDetailView $view): self
    {
        $dto = new self();

        $dto->name = $view->name;

        return $dto;
    }
}
