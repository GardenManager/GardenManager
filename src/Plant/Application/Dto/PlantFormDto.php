<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Dto;

use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Application\View\PlantDetailView;
use Symfony\Component\Validator\Constraints as Assert;

final class PlantFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $localName = null;

    #[Assert\Type(type: 'boolean')]
    public ?bool $isHybrid = null;

    public ?LifecycleEnum $lifecycle = null;

    #[Assert\Length(max: 64)]
    public ?string $genus = null;

    #[Assert\Length(max: 64)]
    public ?string $epithet = null;

    #[Assert\Length(max: 64)]
    public ?string $cultivar = null;

    public static function fromView(PlantDetailView $plantView): self
    {
        $dto = new self();

        $dto->localName = $plantView->localName;
        $dto->isHybrid = $plantView->isHybrid;
        $dto->lifecycle = $plantView->lifecycle;
        $dto->genus = $plantView->genus;
        $dto->epithet = $plantView->epithet;
        $dto->cultivar = $plantView->cultivar;

        return $dto;
    }
}
